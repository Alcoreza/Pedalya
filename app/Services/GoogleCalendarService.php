<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\GoogleCalendarConnection;
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

        $this->syncToConnection(
            $booking,
            $connection,
            'calendar_status',
            'calendar_event_id',
            'calendar_sync_attempts',
            'pedalya-customer:',
        );
    }

    public function syncBookingToManager(Booking $booking): void
    {
        if ($booking->booking_status !== 'confirmed') {
            return;
        }

        $this->syncToConnection(
            $booking,
            $this->managerConnection(),
            'manager_calendar_status',
            'manager_calendar_event_id',
            'manager_calendar_sync_attempts',
            'pedalya-manager:',
        );
    }

    public function managerConnection(): ?GoogleCalendarConnection
    {
        $managerEmail = config('services.google_calendar.manager_email');

        if (! filled($managerEmail)) {
            return null;
        }

        return GoogleCalendarConnection::query()
            ->whereRaw('lower(google_email) = ?', [strtolower(trim($managerEmail))])
            ->whereHas('user', fn ($query) => $query->where('role', 'manager'))
            ->orderBy('user_id')
            ->first();
    }

    private function syncToConnection(
        Booking $booking,
        ?GoogleCalendarConnection $connection,
        string $statusColumn,
        string $eventColumn,
        string $attemptsColumn,
        string $eventNamespace,
    ): void {
        $booking->loadMissing('service');

        if (! $connection || $booking->{$eventColumn}) {
            return;
        }

        $booking->forceFill([$statusColumn => 'pending'])->save();

        try {
            $accessToken = $this->accessToken($connection->refresh_token);
            $eventId = hash('sha256', $eventNamespace.$booking->reference);
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

            $booking->forceFill([
                $statusColumn => 'synchronized',
                $eventColumn => $response->json('id'),
                $attemptsColumn => (int) $booking->{$attemptsColumn} + 1,
            ]);
            $booking->save();
        } catch (Throwable $exception) {
            $booking->forceFill([
                $statusColumn => 'failed',
                $attemptsColumn => (int) $booking->{$attemptsColumn} + 1,
            ]);
            $booking->save();

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
