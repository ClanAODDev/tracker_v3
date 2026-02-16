<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Settings\UserSettings;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Kirschbaum\Commentions\Contracts\Commenter;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User.
 */
class User extends Authenticatable implements Commenter, FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public array $defaultSettings = [
        'snow'                 => 'no_snow',
        'ticket_notifications' => true,
        'disable_animations'   => false,
        'mobile_nav_side'      => 'right',
        'theme'                => 'traditional',
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
        'discord_id',
        'discord_username',
        'date_of_birth',
        'forum_password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'forum_password',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'developer'      => 'boolean',
        'settings'       => 'json',
        'last_login_at'  => 'datetime',
        'date_of_birth'  => 'date',
        'forum_password' => 'encrypted',
        'role'           => Role::class,
    ];

    public function getSettingsAttribute($value): array
    {
        $stored = is_string($value) ? json_decode($value, true) : ($value ?? []);

        return array_merge($this->defaultSettings, $stored);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $user) {
            $user->settings = $user->defaultSettings;
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function divisionApplication()
    {
        return $this->hasOne(DivisionApplication::class);
    }

    public function hasMember(): bool
    {
        return $this->member_id !== null;
    }

    public function isPendingRegistration(): bool
    {
        return $this->discord_id !== null && $this->member_id === null;
    }

    /**
     * @return mixed
     */
    public function scopeAdmins($query)
    {
        $query->whereRole(Role::ADMIN)->orderBy('name', 'ASC');
    }

    public function scopePendingDiscord($query)
    {
        return $query->whereNotNull('discord_id')->whereNull('member_id');
    }

    public function recordActivity(ActivityType $type, $related, array $properties = [])
    {
        if (! method_exists($related, 'recordActivity')) {
            throw new Exception('..');
        }

        return $related->recordActivity($type, $properties);
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

    public function isRole(string|array|Role $role): bool
    {
        $userRole = $this->getEffectiveRole();

        if (! $userRole) {
            return false;
        }

        if ($role instanceof Role) {
            return $userRole === $role;
        }

        $slugs = is_array($role) ? $role : [$role];

        return in_array($userRole->slug(), $slugs, true);
    }

    public function getRole(): ?Role
    {
        return Role::tryFrom($this->role->value);
    }

    public function getEffectiveRole(): ?Role
    {
        if (session('impersonatingRole')) {
            return Role::tryFrom(session('impersonatingRole'));
        }

        return $this->getRole();
    }

    public function isImpersonatingRole(): bool
    {
        return session('impersonatingRole') !== null;
    }

    public function isMember(): bool
    {
        if (! $member = $this->member) {
            return false;
        }

        return $member->position->value == Position::MEMBER->value;
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

    public function assignRole(Role|string|int $role): void
    {
        if ($role instanceof Role) {
            $this->role = $role;
            $this->save();

            return;
        }

        if (is_string($role)) {
            $roleEnum = Role::fromSlug($role);
            if ($roleEnum) {
                $this->role = $roleEnum;
                $this->save();

                return;
            }
        }

        if (is_int($role)) {
            $this->role = $role;
            $this->save();
        }
    }

    /**
     * Is member allowed to remove members from AOD.
     *
     * @return bool
     */
    public function canRemoveUsers()
    {
        return $this->member->rank->value >= Rank::SERGEANT->value;
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

    public function getCommenterName(): string
    {
        return $this->member?->present()->rankName() ?? $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isDeveloper()) {
            return true;
        }

        $panelId = $panel->getId();

        $panelToRoleMapping = [
            'mod'   => ['admin', 'sr_ldr', 'officer'],
            'admin' => 'admin',
        ];

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
        $user       = auth()->user();
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
        $newRank  = $action->rank;

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
        $newRank  = $action->rank;

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

    public static function findOrCreateForMember(Member $member, ?string $email = null): self
    {
        $user = self::where('member_id', $member->id)->first();

        if ($user) {
            if ($email && $user->email !== $email && ! self::where('email', $email)->exists()) {
                $user->update(['email' => $email]);
            }

            return $user;
        }

        if (! $email || self::where('email', $email)->exists()) {
            $email = $member->id . '@placeholder.local';
        }

        $name = strtolower($member->name);
        if (self::where('name', $name)->exists()) {
            $name = $name . '_' . $member->clan_id;
        }

        return self::create([
            'name'      => $name,
            'email'     => $email,
            'member_id' => $member->id,
            'role'      => Role::MEMBER,
        ]);
    }
}
