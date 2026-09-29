<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionHandle extends Model
{
    public $timestamps = false;

    protected $table = 'division_handle';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function handle(): BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }
}
