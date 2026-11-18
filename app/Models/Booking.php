<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'reference',
        'user_id',
        'service_id',
        'customer_name',
        'customer_email',
        'customer_mobile_number',
        'appointment_at',
        'duration_minutes',
        'service_price',
        'bike_notes',
        'consent_confirmed',
        'booking_status',
        'processing_method',
        'review_reason',
        'payment_status',
        'calendar_status',
        'calendar_event_id',
        'calendar_sync_attempts',
        'repair_status',
        'repair_arrived_at',
        'repair_arrived_by',
        'repair_started_at',
        'repair_started_by',
        'repair_completed_at',
        'repair_completed_by',
        'repair_claimed_at',
        'repair_claimed_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'immutable_datetime',
            'service_price' => 'decimal:2',
            'consent_confirmed' => 'boolean',
            'created_at' => 'immutable_datetime',
            'repair_arrived_at' => 'immutable_datetime',
            'repair_started_at' => 'immutable_datetime',
            'repair_completed_at' => 'immutable_datetime',
            'repair_claimed_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function repair(): HasOne
    {
        return $this->hasOne(Repair::class);
    }
}
