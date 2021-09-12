<?php

namespace App\Models;

use App\Models\Census\HasCustomAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Census extends Model
{
    use HasCustomAttributes;
    use HasFactory;

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
