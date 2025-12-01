<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Award extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $award) {
            $award->recipients()->each->delete();
        });
    }

    public function recipients()
    {
        return $this->hasMany(MemberAward::class, 'award_id', 'id')
            ->where('approved', '=', true)
            ->with('member');
    }

    public function unapprovedRecipients()
    {
        return $this->hasMany(MemberAward::class, 'award_id', 'id')
            ->where('approved', '=', false)
            ->with('member');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->select('id', 'name', 'slug');
    }
}
