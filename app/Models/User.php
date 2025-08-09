<?php

namespace App\Models;

use App\Enums\Position;
use App\Enums\Rank;
use App\Settings\UserSettings;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
    public function isRole(string|array $role): bool
    {
        if (!$this->role instanceof Role) {
            return false;
        }

        $roleName = $this->role->name;

        return is_array($role)
            ? in_array($roleName, $role, true)
            : $roleName === $role;
    }

    public function isMember(): bool
    {
        if (!$member = $this->member) {
            return false;
        }

        return $member->position->value == Position::MEMBER->value;
    }

    public function isSquadLeader(): bool
    {
        if (!$member = $this->member) {
            return false;
        }

        return $member->position == Position::SQUAD_LEADER;
    }

    public function isPlatoonLeader(): bool
    {
        if (!$member = $this->member) {
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
        if (!$member = $this->member) {
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
        if ($this->isDeveloper()) {
            return true;
        }

        $panelToRoleMapping = [
            'mod' => ['admin', 'sr_ldr', 'officer'],
            'admin' => 'admin',
            'profile' => ['admin', 'sr_ldr', 'officer', 'member'],
        ];

        $panelId = $panel->getId();

        if (isset($panelToRoleMapping[$panelId])) {
            return $this->isRole($panelToRoleMapping[$panelId]);
        }

        return false;
    }

    private function isWithinPlatoonLimit(Rank $targetRank, $division): bool
    {
        $maxPlRank = Rank::from($division->settings()->get('max_platoon_leader_rank'));

        return $this->isPlatoonLeader() && $targetRank->value <= $maxPlRank->value;
    }

    private function isAdminOrDivisionLeader(): bool
    {
        return $this->isDivisionLeader() || $this->isRole('admin');
    }

    public static function autoApprovedTimestampForRank(
        string $targetRank,
        $division,
        bool $asBoolean = false
    ): bool|null|Carbon {
        $user = auth()->user();
        $targetRank = Rank::from($targetRank);

        // CmdSgt+ authority on Sergeant+ approvals
        if ($user->member->rank->value >= Rank::COMMAND_SERGEANT->value
            && $targetRank->value >= Rank::SERGEANT->value) {
            return $asBoolean ? true : now();
        }

        // Admins can auto-approve for ranks up to Corporal
        if ($user->isRole('admin') && $targetRank->value <= Rank::CORPORAL->value) {
            return $asBoolean ? true : now();
        }

        // Platoon Leaders may auto-approve if the target rank is within their limit
        if ($user->isWithinPlatoonLimit($targetRank, $division)) {
            return $asBoolean ? true : now();
        }

        // Division Leaders can auto-approve for ranks up to Corporal
        if ($user->isDivisionLeader() && $targetRank->value <= Rank::CORPORAL->value) {
            return $asBoolean ? true : now();
        }

        return $asBoolean ? false : null;
    }

    public function canManageTransferCommentsFor(Transfer $transfer): bool
    {

        // Only Division Leaders or Admins can manage comments on transfers
        return $this->isAdminOrDivisionLeader();
    }

    public function canManageRankActionCommentsFor(RankAction $action): bool
    {
        $userRank = $this->member->rank;
        $newRank = $action->rank;

        // For Sergeant and above, require that the user is at least Master Sergeant
        if ($newRank->value >= Rank::SERGEANT->value) {
            return $userRank->value >= Rank::MASTER_SERGEANT->value;
        }

        // Platoon leaders can manage comments for actions within their authorized rank range
        if ($this->isWithinPlatoonLimit($newRank, $this->division)) {
            return true;
        }

        // Otherwise, only Division Leaders or Admins can manage comments
        return $this->isAdminOrDivisionLeader();
    }

    public function canApproveOrDeny(RankAction $action): bool
    {
        $userRank = $this->member->rank;
        $newRank = $action->rank;

        // A user cannot approve/deny a rank action for a rank higher than their own
        if ($newRank->value > $userRank->value) {
            return false;
        }

        // Platoon leaders can approve/deny if within their authorized rank range
        if ($this->isWithinPlatoonLimit($newRank, $this->division)) {
            return true;
        }

        // For Sergeant and above, require that the user is at least Command Sergeant
        if ($newRank->value >= Rank::SERGEANT->value) {
            return $userRank->value >= Rank::COMMAND_SERGEANT->value;
        }

        // For ranks below Sergeant, only Division Leaders or Admins can approve/deny
        return $this->isAdminOrDivisionLeader();
    }
}
