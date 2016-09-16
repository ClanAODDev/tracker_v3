<?php

namespace App;

use App\Settings\UserSettings;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'settings',
        'developer',
        'member_id',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
    ];

    protected $casts = [
        'developer' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * relationship - user belongs to a member
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * relationship - user belongs to a role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Check to see if user is a certain role
     *
     * @param $role
     * @return bool
     */
    public function isRole($role)
    {
        switch ($role) {
            case "admin":
                return $this->role->id === 4 || $this->isDeveloper();

            case "srLeader":
                return $this->role->id === 3;

            case "jrLeader":
                return $this->role->id === 2;

            case "user":
                return $this->role->id === 1;
        }

        throw new InvalidArgumentException('Invalid role used in isRole method.');
    }

    /**
     * @return \App\Settings
     */
    public function settings()
    {
        return new UserSettings($this->settings, $this);
    }

    /**
     * Checks to see if user is a developer
     *
     * @return boolean
     */
    public function isDeveloper()
    {
        return ($this->developer);
    }

    /**
     * Accessor for name
     * enforce proper casing
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
}
