<?php

namespace App\Models;

use App\Models\Handle\HasCustomAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Handle extends Model
{
    use HasCustomAttributes;
    use HasFactory;

    protected $casts = [
        'visible' => 'boolean',
    ];

    protected $guarded = [];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
