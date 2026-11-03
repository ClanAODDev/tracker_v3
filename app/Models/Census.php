<?php

namespace App\Models;

use App\Models\Census\HasCustomAttributes;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Unguarded]
class Census extends Model
{
    use HasCustomAttributes;
    use HasFactory;

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
