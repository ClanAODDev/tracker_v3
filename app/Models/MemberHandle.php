<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MemberHandle extends Pivot
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'handle_member';

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function handle()
    {
        return $this->belongsTo(Handle::class);
    }
}
