<?php

namespace App;

use App\Settings\UserSettings;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        if ( ! $this->role instanceof Role) {
            return false;
        }

        if (is_array($role)) {
            return in_array($this->role->name, $role);
        }

        return $this->role->name === $role;
    }

    public function assignRole($role)
    {
        return $this->role()->associate(
            Role::whereName($role)->firstOrFail()
        );
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
