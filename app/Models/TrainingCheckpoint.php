<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCheckpoint extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TrainingSection::class, 'training_section_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
