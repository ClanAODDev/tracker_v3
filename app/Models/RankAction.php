<?php

namespace App\Models;

use App\Enums\Rank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankAction extends Model
{
    use HasFactory;

    protected $casts = [
        'rank' => Rank::class,
        'approved_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public $guarded = [];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function requester()
    {
        return $this->belongsTo(Member::class);
    }

    public function approve()
    {
        $this->update([
            'approved_at' => now(),
        ]);
    }

    public function accept()
    {
        $this->update([
            'accepted_at' => now(),
        ]);
    }

    public function decline()
    {
        $this->update([
            'declined_at' => now(),
        ]);
    }
}
