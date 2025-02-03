<?php

namespace App\Models;

use App\Enums\Position;
use App\Settings\UserSettings;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User.
 */
class User extends Authenticatable implements FilamentUser
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
        'last_login_at' => 'datetime',
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
    public function scopeAdmins($query)
    {
        $query->whereRoleId(5)->orderBy('name', 'ASC');
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
        if (! method_exists($related, 'recordActivity')) {
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
     *
     * @return bool
     */
    public function isRole($role)
    {
        if (! $this->role instanceof Role) {
            return false;
        }

        if ($this->isDeveloper()) {
            return true;
        }

        if (\is_array($role)) {
            return \in_array($this->role->name, $role, true);
        }

        return $this->role->name === $role;
    }

    public function isSquadLeader(): bool
    {
        if (! $member = $this->member) {
            return false;
        }

        return $member->position == Position::SQUAD_LEADER;
    }

    public function isPlatoonLeader(): bool
    {
        if (! $member = $this->member) {
            return false;
        }

        return $member->position == Position::PLATOON_LEADER;
    }

    public function division()
    {
        return $this->hasOneThrough(Division::class, Member::class, 'id', 'id', 'member_id', 'division_id');
    }

    public function isDivisionLeader(): bool
    {
        if (! $member = $this->member) {
            return false;
        }

        return in_array($member->position, [
            Position::COMMANDING_OFFICER,
            Position::EXECUTIVE_OFFICER,
        ]);
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
     * relationship - user belongs to a role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Is member allowed to remove members from AOD.
     *
     * @return bool
     */
    public function canRemoveUsers()
    {
        return $this->member->rank->value >= \App\Enums\Rank::SERGEANT->value;
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

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() == 'mod') {
            return $this->isRole(['admin', 'sr_ldr', 'officer']);
        }

        if ($panel->getId() == 'admin') {
            return $this->isRole('admin');
        }

        return false;
    }
}
