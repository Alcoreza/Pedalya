<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleCalendarService
{
    public function syncBooking(Booking $booking): void
    {
        $booking->loadMissing(['user.googleCalendarConnection', 'service']);
        $connection = $booking->user?->googleCalendarConnection;

        if (! $connection || $booking->calendar_event_id) {
            return;
        }

        $booking->update(['calendar_status' => 'pending']);

        try {
            $accessToken = $this->accessToken($connection->refresh_token);
            $eventId = hash('sha256', 'pedalya:'.$booking->reference);
            $startsAt = $booking->appointment_at->setTimezone(config('booking.timezone', 'Asia/Manila'));
            $endsAt = $startsAt->addMinutes((int) $booking->duration_minutes);
            $location = 'Lucban-Sampaloc Road, Lucban, Quezon';
            $event = [
                'id' => $eventId,
                'summary' => ($booking->service?->name ?? 'Bike repair').' - Pedalya Bike Repair',
                'description' => implode("\n", [
                    'Pedalya bike repair appointment.',
                    'Booking reference: '.$booking->reference,
                    'Payment status: '.ucfirst($booking->payment_status).'.',
                ]),
                'location' => $location,
                'start' => [
                    'dateTime' => $startsAt->toRfc3339String(),
                    'timeZone' => config('booking.timezone', 'Asia/Manila'),
                ],
                'end' => [
                    'dateTime' => $endsAt->toRfc3339String(),
                    'timeZone' => config('booking.timezone', 'Asia/Manila'),
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [['method' => 'popup', 'minutes' => 60]],
                ],
            ];

            $url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';
            $response = Http::connectTimeout(3)->timeout(10)->withToken($accessToken)->acceptJson()->post($url, $event);

            // A retry after a network timeout may find the deterministic event already created.
            if ($response->status() === 409) {
                $response = Http::connectTimeout(3)->timeout(10)->withToken($accessToken)->acceptJson()->get($url.'/'.$eventId);
            }

            if (! $response->successful() || ! $response->json('id')) {
                throw new RuntimeException('Google Calendar did not return a saved event.');
            }

            $booking->update([
                'calendar_status' => 'synchronized',
                'calendar_event_id' => $response->json('id'),
                'calendar_sync_attempts' => (int) $booking->calendar_sync_attempts + 1,
            ]);
        } catch (Throwable $exception) {
            $booking->update([
                'calendar_status' => 'failed',
                'calendar_sync_attempts' => (int) $booking->calendar_sync_attempts + 1,
            ]);

            Log::warning('Google Calendar booking sync failed.', [
                'booking_reference' => $booking->reference,
                'user_id' => $booking->user_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function accessToken(string $refreshToken): string
    {
        $response = Http::connectTimeout(3)->timeout(10)->asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $token = $response->json('access_token');
        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new RuntimeException('Could not refresh Google Calendar authorization.');
        }

        return $token;
    }
}
