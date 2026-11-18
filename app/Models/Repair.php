<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repair extends Model
{
    protected $table = 'repairs';

    protected $fillable = [
        'booking_id',
        'status',
        'arrived_at', 'arrived_by',
        'started_at', 'started_by',
        'completed_at', 'completed_by',
        'claimed_at', 'claimed_by',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function arrivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arrived_by');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
