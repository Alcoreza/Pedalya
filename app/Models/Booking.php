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
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'immutable_datetime',
            'service_price' => 'decimal:2',
            'consent_confirmed' => 'boolean',
            'created_at' => 'immutable_datetime',
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
}
