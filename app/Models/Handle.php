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

    protected $fillable = [
        'label',
        'type',
        'comment',
        'url',
    ];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    //    public function divisionHandle($handleId)
    //    {
    //        return $this->handles()->wherePivot('handle_id', $handleId);
    //    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
