<?php

namespace App\Models;

use App\Enums\PromotionDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'decision' => PromotionDecision::class
    ];
}
