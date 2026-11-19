<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GoogleCalendarConnection;
use App\Services\GoogleCalendarService;
use App\Services\Manager\CalendarSyncRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleCalendarConnectionController extends Controller
{
    public function connect(Request $request): RedirectResponse
    {
        $returnTo = $request->query('return_to') === 'schedule' ? 'schedule' : 'profile';
        return $this->beginConnection($request, $returnTo);
    }

    public function connectForManager(Request $request): RedirectResponse
    {
        return $this->beginConnection($request, 'manager_schedule');
    }

    private function beginConnection(Request $request, string $returnTo): RedirectResponse
    {
        $request->session()->put('google_calendar_return_to', $returnTo);

        if (! $this->isConfigured()) {
            return $this->returnTo($request, $returnTo, 'Google Calendar is not configured yet. Add the Google OAuth credentials to the app environment first.', true);
        }

        $state = Str::random(48);
        $request->session()->put('google_calendar_oauth_state', $state);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google_calendar.client_id'),
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', config('services.google_calendar.scope', [])),
            'access_type' => 'offline',
            'prompt' => $returnTo === 'manager_schedule' ? 'select_account consent' : 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]));
    }

    public function callback(Request $request, GoogleCalendarService $calendar, CalendarSyncRunner $managerCalendar): RedirectResponse
    {
        $returnTo = $request->session()->get('google_calendar_return_to', 'profile');
        $expectedState = (string) $request->session()->pull('google_calendar_oauth_state', '');
        $providedState = (string) $request->query('state', '');

        if ($expectedState === '' || $providedState === '' || ! hash_equals($expectedState, $providedState)) {
            return $this->returnTo($request, $returnTo, 'Google Calendar connection could not be verified. Please try again.', true);
        }

        if ($request->filled('error')) {
            return $this->returnTo($request, $returnTo, 'Google Calendar access was not granted.', true);
        }

        $tokenResponse = Http::connectTimeout(3)->timeout(10)->asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'code' => $request->query('code'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
        ]);

        $accessToken = $tokenResponse->json('access_token');
        if (! $tokenResponse->successful() || ! is_string($accessToken) || $accessToken === '') {
            return $this->returnTo($request, $returnTo, 'Google Calendar could not be connected. Check the OAuth credentials and callback URL, then try again.', true);
        }

        $googleUserResponse = Http::connectTimeout(3)->timeout(10)->withToken($accessToken)->acceptJson()->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $googleEmail = $googleUserResponse->json('email');
        if (! $googleUserResponse->successful() || ! is_string($googleEmail) || $googleEmail === '') {
            return $this->returnTo($request, $returnTo, 'Google did not return an account email. Please try connecting again.', true);
        }

        if ($returnTo === 'manager_schedule') {
            $managerEmail = strtolower(trim((string) config('services.google_calendar.manager_email')));

            if ($managerEmail === '') {
                return $this->returnTo($request, $returnTo, 'Set GOOGLE_CALENDAR_MANAGER_EMAIL in the app environment before connecting the manager calendar.', true);
            }

            if (! hash_equals($managerEmail, strtolower(trim($googleEmail)))) {
                return $this->returnTo($request, $returnTo, 'Choose the manager Google account ('.config('services.google_calendar.manager_email').') to connect this calendar.', true);
            }
        }

        $user = $request->user();
        $connection = $user->googleCalendarConnection;
        $refreshToken = $tokenResponse->json('refresh_token')
            ?: ($connection?->google_email === $googleEmail ? $connection?->refresh_token : null);

        if (! $refreshToken) {
            return $this->returnTo($request, $returnTo, 'Google did not provide a refresh token. Revoke Pedalya access in your Google account and connect again.', true);
        }

        $grantedScopes = $tokenResponse->json('scope');
        GoogleCalendarConnection::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'google_email' => $googleEmail,
                'refresh_token' => $refreshToken,
                'scopes' => is_string($grantedScopes) ? $grantedScopes : implode(' ', config('services.google_calendar.scope', [])),
            ],
        );

        if ($returnTo === 'manager_schedule') {
            $confirmedBookings = Booking::query()
                ->where('booking_status', 'confirmed')
                ->with('service')
                ->orderBy('appointment_at')
                ->get();

            $confirmedBookings->each(fn (Booking $booking) => $managerCalendar->run($booking, $user));
            $failedSyncCount = $confirmedBookings
                ->filter(fn (Booking $booking): bool => $booking->fresh()->manager_calendar_status === 'failed')
                ->count();
            $status = $failedSyncCount > 0
                ? "Google Calendar connected for {$googleEmail}; {$failedSyncCount} confirmed booking(s) need a sync retry."
                : "Google Calendar connected for {$googleEmail}. Confirmed bookings were synced.";

            return $this->returnTo($request, $returnTo, $status);
        }

        $upcomingBookings = $user->bookings()
            ->where('appointment_at', '>=', now())
            ->whereNull('calendar_event_id')
            ->whereNotIn('booking_status', ['rejected', 'cancelled', 'completed'])
            ->with('service')
            ->orderBy('appointment_at')
            ->get();
        $upcomingBookings->each(fn ($booking) => $calendar->syncBooking($booking));

        $failedSyncCount = $upcomingBookings->filter(fn ($booking) => $booking->fresh()->calendar_status === 'failed')->count();
        $status = $failedSyncCount > 0
            ? "Google Calendar connected, but {$failedSyncCount} booking(s) could not be synced. Check the connection and try again."
            : "Google Calendar connected for {$googleEmail}. Upcoming bookings were synced.";

        return $this->returnTo($request, $returnTo, $status);
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->googleCalendarConnection()->delete();

        return redirect()->route('customer.profile')->with('status', 'Google Calendar was disconnected. Existing events in your Google Calendar were left unchanged.');
    }

    public function disconnectForManager(Request $request): RedirectResponse
    {
        $request->user()->googleCalendarConnection()->delete();

        return redirect()->route('manager.schedule.index')->with('schedule_notice', 'Your Google Calendar account was disconnected. Existing events were left unchanged.');
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google_calendar.client_id'))
            && filled(config('services.google_calendar.client_secret'))
            && filled(config('services.google_calendar.redirect_uri'));
    }

    private function returnTo(Request $request, string $returnTo, string $message, bool $isError = false): RedirectResponse
    {
        $request->session()->forget('google_calendar_return_to');
        $route = match ($returnTo) {
            'schedule' => 'customer.schedule',
            'manager_schedule' => 'manager.schedule.index',
            default => 'customer.profile',
        };
        $flashKey = $returnTo === 'manager_schedule'
            ? 'schedule_notice'
            : ($isError ? 'calendar_error' : ($returnTo === 'schedule' ? 'calendar_message' : 'status'));

        $redirect = redirect()->route($route)->with($flashKey, $message);

        return $returnTo === 'schedule'
            ? $redirect->with('calendar_returned', true)
            : $redirect;
    }
}
