<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Services\Manager\ManagerPresenter;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManagerSchedulePageTest extends TestCase
{
    private User $manager;
    private User $customer;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('booking.timezone', 'Asia/Manila');
        Carbon::setTestNow(Carbon::parse('2026-11-19 09:00:00', 'Asia/Manila'));
        $this->createDomainSchema();

        $this->manager = User::query()->create([
            'name' => 'Elmer Daniel', 'email' => 'manager@example.com',
            'mobile_number' => '+63 917 000 0001', 'password' => 'password',
            'role' => 'manager', 'created_at' => now(),
        ]);
        $this->customer = User::query()->create([
            'name' => 'Ana Reyes', 'email' => 'ana@example.com',
            'mobile_number' => '+63 917 000 0002', 'password' => 'password',
            'role' => 'customer', 'created_at' => now(),
        ]);
        $this->service = Service::query()->create([
            'slug' => 'brake-inspection', 'name' => 'Brake inspection',
            'description' => 'Brake inspection service', 'duration_minutes' => 45,
            'price' => 500, 'capacity_per_slot' => 1, 'is_active' => true,
        ]);

        $this->actingAs($this->manager);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_schedule_shows_confirmed_and_manager_review_records_but_not_rejected_records(): void
    {
        $this->booking('PDL-CONFIRMED', 'confirmed', 'automatic', dayOffset: 1);
        $this->booking('PDL-REVIEW', 'pending', 'review_required', dayOffset: 2);
        $this->booking('PDL-REJECTED', 'rejected', 'manual', dayOffset: 3);

        $response = $this->get(route('manager.schedule.index', ['date' => '2026-11-19']));

        $response
            ->assertOk()
            ->assertSee('PDL-CONFIRMED')
            ->assertSee('PDL-REVIEW')
            ->assertSee('Manager review')
            ->assertSee('schedule-card-manager_review', false)
            ->assertDontSee('PDL-REJECTED')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['confirmed'] === 1
                && $summary['manager_review'] === 1);
    }

    public function test_rejecting_a_review_booking_removes_it_from_the_schedule(): void
    {
        $booking = $this->booking(
            'PDL-REVIEW', 'pending', 'review_required',
            calendarStatus: 'pending', syncAttempts: 2,
        );
        $this->assertSame(1, ManagerPresenter::pendingBookings());

        $this->post(route('manager.bookings.decision', $booking->reference), [
            'decision' => 'rejected',
        ])->assertRedirect(route('manager.bookings.index', ['booking' => $booking->reference]))
            ->assertSessionHas('booking_notice', fn (string $notice): bool =>
                str_contains($notice, $booking->reference)
                && str_contains($notice, 'removed from the workshop schedule')
            );

        $this->assertDatabaseHas('bookings', [
            'reference' => 'PDL-REVIEW', 'booking_status' => 'rejected',
            'processing_method' => 'manual', 'calendar_status' => 'not_applicable',
            'calendar_event_id' => null, 'calendar_sync_attempts' => 0,
        ]);

        $this->get(route('manager.schedule.index', ['date' => '2026-11-19']))
            ->assertOk()
            ->assertDontSee('PDL-REVIEW');
        $this->assertSame(0, ManagerPresenter::pendingBookings());
    }

    public function test_confirmation_without_a_calendar_connection_does_not_consume_a_sync_attempt(): void
    {
        $booking = $this->booking('PDL-REVIEW', 'pending', 'review_required');

        $this->post(route('manager.bookings.decision', $booking->reference), [
            'decision' => 'confirmed',
        ])->assertRedirect(route('manager.bookings.index', ['booking' => $booking->reference]));

        $this->assertDatabaseHas('bookings', [
            'reference' => 'PDL-REVIEW', 'booking_status' => 'confirmed',
            'processing_method' => 'manual', 'calendar_status' => 'not_applicable',
            'calendar_sync_attempts' => 0,
        ]);
    }

    private function booking(
        string $reference,
        string $status,
        string $processingMethod,
        int $dayOffset = 1,
        string $calendarStatus = 'not_applicable',
        int $syncAttempts = 0,
    ): Booking {
        return Booking::query()->create([
            'reference' => $reference, 'user_id' => $this->customer->id,
            'service_id' => $this->service->id, 'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_mobile_number' => $this->customer->mobile_number,
            'appointment_at' => Carbon::parse('2026-11-16 10:00:00', 'Asia/Manila')->addDays($dayOffset)->utc(),
            'duration_minutes' => 45, 'service_price' => 500,
            'bike_notes' => 'Front brake needs inspection.', 'consent_confirmed' => true,
            'booking_status' => $status, 'processing_method' => $processingMethod,
            'review_reason' => $processingMethod === 'review_required' ? 'capacity_reached' : null,
            'payment_status' => 'pending', 'calendar_status' => $calendarStatus,
            'calendar_sync_attempts' => $syncAttempts, 'created_at' => now()->utc(),
        ]);
    }

    private function createDomainSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('mobile_number')->nullable();
            $table->string('email')->unique(); $table->string('password');
            $table->string('role')->default('customer'); $table->timestamp('created_at')->nullable();
        });
        Schema::create('services', function (Blueprint $table): void {
            $table->id(); $table->string('slug')->unique(); $table->string('name');
            $table->text('description')->default(''); $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 10, 2); $table->unsignedInteger('capacity_per_slot')->default(1);
            $table->boolean('is_active')->default(true);
        });
        Schema::create('google_calendar_connections', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique(); $table->string('google_email');
            $table->text('refresh_token'); $table->text('scopes')->default(''); $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id(); $table->string('reference')->unique(); $table->foreignId('user_id');
            $table->foreignId('service_id'); $table->string('customer_name');
            $table->string('customer_email'); $table->string('customer_mobile_number');
            $table->timestamp('appointment_at'); $table->unsignedInteger('duration_minutes');
            $table->decimal('service_price', 10, 2); $table->text('bike_notes')->nullable();
            $table->boolean('consent_confirmed')->default(false); $table->string('booking_status')->default('pending');
            $table->string('processing_method')->default('automatic'); $table->string('review_reason')->nullable();
            $table->string('payment_status')->default('pending'); $table->string('calendar_status')->default('not_applicable');
            $table->string('calendar_event_id')->nullable(); $table->unsignedSmallInteger('calendar_sync_attempts')->default(0);
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->id(); $table->foreignId('booking_id')->unique(); $table->string('status')->default('pending');
        });
        Schema::create('repairs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('booking_id')->unique(); $table->string('status');
            $table->timestamp('arrived_at')->nullable(); $table->foreignId('arrived_by')->nullable();
            $table->timestamp('started_at')->nullable(); $table->foreignId('started_by')->nullable();
            $table->timestamp('completed_at')->nullable(); $table->foreignId('completed_by')->nullable();
            $table->timestamp('claimed_at')->nullable(); $table->foreignId('claimed_by')->nullable(); $table->timestamps();
        });
        Schema::create('activity_events', function (Blueprint $table): void {
            $table->id(); $table->string('event_key'); $table->string('category'); $table->string('result');
            $table->string('title'); $table->text('summary')->default(''); $table->text('detail')->default('');
            $table->foreignId('booking_id')->nullable(); $table->string('booking_reference')->nullable();
            $table->string('provider')->nullable(); $table->string('provider_reference')->nullable();
            $table->foreignId('actor_user_id')->nullable(); $table->string('actor_label')->nullable();
            $table->string('source'); $table->json('metadata')->nullable();
            $table->string('idempotency_key')->nullable()->unique(); $table->timestamp('occurred_at')->useCurrent();
        });
    }
}
