<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSergeant extends Model
{
    protected $table = 'division_staffSergeants';

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
