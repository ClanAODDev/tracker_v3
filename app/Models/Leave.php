<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $casts = [
        'extended' => 'boolean',
        'end_date' => 'datetime',
    ];

    public static array $reasons = [
        'military'  => 'Military',
        'medical'   => 'Medical',
        'education' => 'Education',
        'travel'    => 'Travel',
        'other'     => 'Other',
    ];

    protected $guarded = [];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getExpiredAttribute(): bool
    {
        return today() > $this->end_date->format('Y-m-d');
    }

    public function getDateAttribute(): string
    {
        return $this->end_date->format('Y-m-d');
    }
}
