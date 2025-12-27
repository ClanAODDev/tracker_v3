<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function module(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(TrainingCheckpoint::class)->orderBy('display_order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
