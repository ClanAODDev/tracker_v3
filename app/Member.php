<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{

    protected $dates = ['join_date', 'last_forum_login'];

    use SoftDeletes;

    /**
     * relationship - user has one member
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Accessor for name
     * enforce proper casing
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Returns
     * @return string
     */
    public function getSpecialNameAttribute()
    {
        if ($this->position) {
            $title = ($this->position->name) ?: null;
            $icon = ($this->position->icon) ? "<i class=\"fa fa-{$this->position->icon}\"></i>" : null;

            return "<span title=\"{$title}\" class=\"{$this->position->class}\">{$icon} {$this->name}</span>";
        }
        return $this->name;
    }

    /**
     * relationship - member has many divisions
     */
    public function divisions()
    {
        return $this->belongsToMany(Division::class)->withPivot('primary');
    }

    /**
     * Return a carbon formatted date
     * @param $value
     * @return string
     */
    public function getJoinDateAttribute($value)
    {
        return Carbon::parse($value)->toFormattedDateString();
    }

    /**
     * relationship - member belongs to a platoon
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    /**
     * relationship - member belongs to a rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }


    /**
     * relationship - member belongs to a position
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * relationship - member belongs to a squad
     */
    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * Gets member's primary division
     */
    public function primaryDivision()
    {
        return $this->divisions()->wherePivot('primary', true)->first();
    }

}
