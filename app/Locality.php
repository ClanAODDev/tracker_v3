<?php

namespace App;

use App\Division;
use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    /**
     * Division translation model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
