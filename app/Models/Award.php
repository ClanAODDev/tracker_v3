<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    // id
    // name
    // filename / slug
    // group_id -- for grouping certain awards (IE tenure based awards, 5-year, 10-year, etc)
    // rank  -- if an award has a group, only the highest rank of that award will be shown
    // timestamps
}
