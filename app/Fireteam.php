<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Fireteam
 *
 * @package App
 */
class Fireteam extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('playersCount', function ($builder) {
            $builder->withCount('players');
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Member');
    }

    public function getSlotsAvailableAttribute()
    {
        // count the owner as a slot
        return $this->players_needed - $this->players_count;
    }

    public function getSpotsColorAttribute()
    {
        switch ($this->slotsAvailable) {
            case 5:
            case 4:
            case 3:
            return 'text-success';
            case 2:
                return 'text-warning';
            case 1:
                return 'text-danger';
        }

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function players()
    {
        return $this->belongsToMany('App\Member', 'fireteam_member')->withPivot('light');
    }
}
