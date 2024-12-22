<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function recipients()
    {
        return $this->belongsToMany(Member::class)
            ->select('rank', 'name');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->select('name');
    }
}
