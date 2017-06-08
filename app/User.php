<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Settings\UserSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @package App
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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
    ];

    /**
     * @var array
     */
    protected $casts = [
        'developer' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'last_login_at'
    ];

    /**
     * relationship - user belongs to a member
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Record new activity for the user.
     *
     * @param  string $name
     * @param  mixed $related
     * @return mixed
     * @throws \Exception
     */
    public function recordActivity($name, $related)
    {
        if (! method_exists($related, 'recordActivity')) {
            throw new \Exception('..');
        }
        return $related->recordActivity($name);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Check to see if user is a certain role
     *
     * @param $role
     * @return bool
     */
    public function isRole($role)
    {
        if ( ! $this->role instanceof Role) {
            return false;
        }

        if ($this->isDeveloper()) {
            return true;
        }

        if (is_array($role)) {
            return in_array($this->role->name, $role);
        }

        return $this->role->name === $role;
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
     * Assign a role to a user
     *
     * @param $role
     */
    public function assignRole(Role $role)
    {
        $this->role()->associate($role)->save();
    }

    /**
     * relationship - user belongs to a role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Is member allowed to remove members from AOD
     *
     * @return bool
     */
    public function canRemoveUsers()
    {
        return $this->member->rank_id >= 9;
    }

    /**
     * @return UserSettings
     */
    public function settings()
    {
        return new UserSettings($this->settings, $this);
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
