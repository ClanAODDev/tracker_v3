<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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

    public function getRarity(?int $count = null): string
    {
        $count ??= $this->recipients_count ?? $this->recipients()->count();

        return match (true) {
            $count === 0 => 'mythic',
            $count <= 10 => 'legendary',
            $count <= 30 => 'epic',
            $count <= 50 => 'rare',
            default => 'common',
        };
    }

    public function getImagePath(): string
    {
        if ($this->image && Storage::exists($this->image)) {
            return asset(Storage::url($this->image));
        }

        return asset(config('app.logo'));
    }
}
