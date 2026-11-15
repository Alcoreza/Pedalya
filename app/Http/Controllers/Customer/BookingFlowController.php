<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Services\Bookings\BookingAvailabilityService;
use App\Services\GoogleCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingFlowController extends Controller
{
    public function __construct(
        private readonly BookingAvailabilityService $availability,
        private readonly GoogleCalendarService $googleCalendar,
    )
    {
    }

    public function services(): View
    {
        return view('customer.book', [
            'services' => Service::query()->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function chooseService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
        ]);

        $request->session()->put('booking_flow', [
            'service_id' => (int) $validated['service_id'],
        ]);

        return redirect()->route('customer.schedule');
    }

    public function schedule(Request $request): View|RedirectResponse
    {
        $service = $this->selectedService($request);
        if (! $service) {
            return redirect()->route('customer.book')->withErrors(['service_id' => 'Choose a service to continue.']);
        }

        $dateOptions = $this->availability->dateOptions($service);
        $requestedDate = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']])['date'] ?? null;
        $selectedOption = $requestedDate
            ? $dateOptions->firstWhere('key', $requestedDate)
            : null;
        $selectedOption ??= $dateOptions->first(fn (array $option): bool => $option['available_slots'] > 0)
            ?? $dateOptions->first();

        if (! $selectedOption) {
            return view('customer.schedule', [
                'service' => $service,
                'dateOptions' => $dateOptions,
                'dateOptionsData' => [],
                'selectedDate' => null,
                'availableSlots' => [],
                'calendarConnection' => $request->user()->googleCalendarConnection,
            ]);
        }

        $selectedDate = $selectedOption['date'];

        return view('customer.schedule', [
            'service' => $service,
            'dateOptions' => $dateOptions,
            'dateOptionsData' => $dateOptions->map(fn (array $option): array => [
                'key' => $option['key'],
                'weekday' => $option['weekday'],
                'day' => $option['day'],
                'month_label' => $option['month_label'],
                'long_label' => $option['long_label'],
                'slots' => $option['slots'],
            ])->values()->all(),
            'selectedDate' => $selectedDate,
            'availableSlots' => $selectedOption['slots'],
            'calendarConnection' => $request->user()->googleCalendarConnection,
        ]);
    }

    public function saveSchedule(Request $request): RedirectResponse
    {
        $service = $this->selectedService($request);
        if (! $service) {
            return redirect()->route('customer.book')->withErrors(['service_id' => 'Choose a service to continue.']);
        }

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $startsAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $validated['date'].' '.$validated['time'],
            config('booking.timezone'),
        );

        if (! $startsAt || ! $this->availability->isAvailable($service, $startsAt)) {
            return redirect()
                ->route('customer.schedule', ['date' => $validated['date']])
                ->withErrors(['time' => 'That time is no longer available. Please choose another slot.']);
        }

        $request->session()->put('booking_flow.appointment_at', $startsAt->toIso8601String());

        return redirect()->route('customer.review');
    }

    public function review(Request $request): View|RedirectResponse
    {
        $service = $this->selectedService($request);
        $appointmentAt = $request->session()->get('booking_flow.appointment_at');

        if (! $service || ! $appointmentAt) {
            return redirect()->route('customer.schedule')->withErrors(['booking' => 'Choose a service and appointment time first.']);
        }

        return view('customer.review', [
            'service' => $service,
            'appointmentAt' => CarbonImmutable::parse($appointmentAt)->setTimezone(config('booking.timezone')),
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        $flow = $request->session()->get('booking_flow', []);
        $serviceId = (int) ($flow['service_id'] ?? 0);
        $appointmentAt = $flow['appointment_at'] ?? null;
        $startsAt = $appointmentAt
            ? CarbonImmutable::parse($appointmentAt)->setTimezone(config('booking.timezone'))
            : null;

        if (! $serviceId || ! $startsAt) {
            return redirect()->route('customer.book')->withErrors(['booking' => 'Your booking selection expired. Please start again.']);
        }

        $booking = DB::transaction(function () use ($serviceId, $startsAt, $validated, $request): ?Booking {
            // Serialize slot checks so two customers cannot take the final place at once.
            DB::select('SELECT pg_advisory_xact_lock(74122201)');

            $service = Service::query()->where('is_active', true)->findOrFail($serviceId);
            if (! $this->availability->isAvailable($service, $startsAt)) {
                return null;
            }

            $user = $request->user();
            $booking = Booking::query()->create([
                'reference' => 'PDL-'.Str::upper(Str::random(10)),
                'user_id' => $user->id,
                'service_id' => $service->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_mobile_number' => $user->mobile_number,
                'appointment_at' => $startsAt->utc(),
                'duration_minutes' => $service->duration_minutes,
                'service_price' => $service->price,
                'bike_notes' => $validated['notes'] ?? null,
                'consent_confirmed' => true,
                'booking_status' => 'pending',
                'payment_status' => 'pending',
                'calendar_status' => 'not_applicable',
            ]);

            Payment::query()->create([
                'booking_id' => $booking->id,
                'provider' => 'paymongo',
                'amount_minor' => (int) round((float) $service->price * 100),
                'currency' => 'PHP',
                'status' => 'pending',
            ]);

            return $booking;
        });

        if (! $booking) {
            return redirect()
                ->route('customer.schedule', ['date' => $startsAt->format('Y-m-d')])
                ->withErrors(['time' => 'That appointment time was just taken. Please choose another slot.']);
        }

        $this->googleCalendar->syncBooking($booking);

        $request->session()->forget('booking_flow');

        return redirect()->route('customer.confirmation', ['reference' => $booking->reference]);
    }

    public function confirmation(Request $request, string $reference): View
    {
        $booking = Booking::query()
            ->with(['service', 'payment', 'user.googleCalendarConnection'])
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('customer.confirmation', ['booking' => $booking]);
    }

    private function selectedService(Request $request): ?Service
    {
        $serviceId = $request->session()->get('booking_flow.service_id');

        return $serviceId
            ? Service::query()->where('is_active', true)->find($serviceId)
            : null;
    }
}
