<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('division_handle')]
#[WithoutTimestamps]
#[Unguarded]
class DivisionHandle extends Model
{
    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function handle(): BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }
}
