<?php

namespace App\Models;

use App\Models\Census\HasCustomAttributes;
use Illuminate\Database\Eloquent\Model;

class Census extends Model
{
    use HasCustomAttributes;

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
