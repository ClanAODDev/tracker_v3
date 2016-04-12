<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

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
     * Check to see if user is a certain role
     *
     * @param $role
     * @return bool
     */
    public function isRole($role)
    {
        switch ($role) {
            case "admin":
                return ($this->role->id === 4);
            case "srLeader":
                return ($this->role->id === 3);
            case "jrLeader":
                return ($this->role->id === 2);
            case "user":
                return ($this->role->id === 1);
        }
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
