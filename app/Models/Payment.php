<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'provider',
        'provider_checkout_session_id',
        'provider_payment_intent_id',
        'provider_payment_id',
        'checkout_url',
        'amount_minor',
        'currency',
        'status',
        'webhook_event_id',
        'paid_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'paid_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
