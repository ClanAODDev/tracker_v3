<?php

namespace App\Models;

use App\Models\Handle\HasCustomAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handle extends Model
{
    use HasCustomAttributes;
    use HasFactory;

    protected $casts = [
        'visible' => 'boolean',
    ];

    protected $guarded = [];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
