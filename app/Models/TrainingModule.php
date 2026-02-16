<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'            => 'boolean',
        'show_completion_form' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(TrainingSection::class)->orderBy('display_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
