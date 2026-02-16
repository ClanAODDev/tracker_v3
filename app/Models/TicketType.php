<?php

namespace App\Models;

use App\Enums\Rank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'role_access'                     => 'json',
        'minimum_rank'                    => Rank::class,
        'include_content_in_notification' => 'boolean',
    ];

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function auto_assign_to()
    {
        return $this->belongsTo(User::class);
    }

    public function userCanWork(User $user): bool
    {
        if ($user->isRole('admin')) {
            return true;
        }

        if (! $this->minimum_rank) {
            return false;
        }

        $member = $user->member;
        if (! $member) {
            return false;
        }

        return $member->rank->value >= $this->minimum_rank->value;
    }
}
