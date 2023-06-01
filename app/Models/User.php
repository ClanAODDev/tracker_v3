<?php

namespace App\Models;

use App\Enums\Role;
use App\Settings\UserSettings;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User.
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public array $defaultSettings = [
        'snow' => false,
        'ticket_notifications' => true,
    ];

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
        'role' => Role::class,
    ];

    /**
     * @var array
     */
    protected $dates = [
        'last_login_at',
    ];

    public static function boot()
    {
        parent::boot();

        /*
         * Handle default settings population.
         */
        static::creating(function (self $user) {
            $user->settings = $user->defaultSettings;
        });
    }

    /**
     * relationship - user belongs to a member.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return mixed
     */
    public function scopeAdmins()
    {
        return $this->whereRoleId(5)->orderBy('name', 'ASC');
    }

    /**
     * Record new activity for the user.
     *
     * @param  string  $name
     * @param  mixed  $related
     * @return mixed
     *
     * @throws Exception
     */
    public function recordActivity($name, $related)
    {
        if (!method_exists($related, 'recordActivity')) {
            throw new Exception('..');
        }

        return $related->recordActivity($name);
    }

    /**
     * @return HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * @return HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Check to see if user is a certain role.
     */
    public function isRole(mixed $role): bool
    {
        if ($role instanceof \BackedEnum) {
            return $this->role === $role;
        }

        if ($this->isDeveloper()) {
            return true;
        }

        $lowercase_user_role = strtolower($this->role->name);

        if (\is_array($role)) {
            return \in_array($lowercase_user_role, $role);
        }

        if (is_string($role)) {
            return $lowercase_user_role === $role;
        }

        return false;
    }

    /**
     * Checks to see if user is a developer.
     *
     * @return bool
     */
    public function isDeveloper()
    {
        return $this->developer;
    }

    /**
     * Assign a role to a user.
     *
     * @param $role
     */
    public function assignRole($role)
    {
        if ($role instanceof Role) {
            $this->role()->associate($role)->save();

            return;
        }

        if (\is_string($role)) {
            $role = Role::whereName(strtolower($role))->firstOrFail();
        }

        if (\is_int($role)) {
            $role = Role::find($role);
        }

        $this->role()->associate($role)->save();
    }

    /**
     * Is member allowed to remove members from AOD.
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
     * enforce proper casing.
     *
     * @param  mixed  $value
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getPositionAttribute()
    {
        return $this->member->position;
    }

    public function squad()
    {
        return $this->hasOneThrough(Squad::class, Member::class, 'id', 'id', 'member_id', 'squad_id');
    }

    public function platoon()
    {
        return $this->hasOneThrough(Platoon::class, Member::class, 'id', 'id', 'member_id', 'platoon_id');
    }

    public function division()
    {
        return $this->hasOneThrough(Division::class, Member::class, 'id', 'id', 'member_id', 'division_id');
    }
}
