<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Settings\UserSettings;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Kirschbaum\Commentions\Contracts\Commenter;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements Commenter, FilamentUser, HasAvatar
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

    protected $hidden = [
        'password',
        'remember_token',
        'forum_password',
    ];

    protected $casts = [
        'developer'      => 'boolean',
        'settings'       => 'json',
        'last_login_at'  => 'datetime',
        'date_of_birth'  => 'date',
        'forum_password' => 'encrypted',
        'role'           => Role::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            $user->settings = $user->defaultSettings;
        });
    }

    public function getSettingsAttribute($value): array
    {
        $stored = is_string($value) ? json_decode($value, true) : ($value ?? []);

        return array_merge($this->defaultSettings, $stored);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function divisionApplication(): HasOne
    {
        return $this->hasOne(DivisionApplication::class);
    }

    public function division(): HasOneThrough
    {
        return $this->hasOneThrough(Division::class, Member::class, 'id', 'id', 'member_id', 'division_id');
    }

    public function activity(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function scopeAdmins($query): void
    {
        $query->whereRole(Role::ADMIN)->orderBy('name', 'ASC');
    }

    public function scopePendingDiscord($query): void
    {
        $query->whereNotNull('discord_id')->whereNull('member_id');
    }

    public function hasMember(): bool
    {
        return $this->member_id !== null;
    }

    public function isPendingRegistration(): bool
    {
        return $this->discord_id !== null && $this->member_id === null;
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

        $items = is_array($role) ? $role : [$role];
        $slugs = array_map(fn ($r) => $r instanceof Role ? $r->slug() : $r, $items);

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

    public function isDeveloper(): bool
    {
        return (bool) $this->developer;
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

    public function canRemoveUsers(): bool
    {
        return $this->member->rank->value >= Rank::SERGEANT->value;
    }

    public function settings(): UserSettings
    {
        return new UserSettings($this->settings, $this);
    }

    public function getNameAttribute($value): string
    {
        return ucfirst($value);
    }

    public function getCommenterName(): string
    {
        return $this->member?->present()->rankName() ?? $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->member?->getDiscordAvatarUrl();
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

    public function recordActivity(ActivityType $type, mixed $related, array $properties = []): mixed
    {
        if (! method_exists($related, 'recordActivity')) {
            throw new InvalidArgumentException(get_class($related) . ' does not support activity recording.');
        }

        return $related->recordActivity($type, $properties);
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

        return $this->isAdminOrDivisionLeader();
    }

    public function canApproveOrDeny(RankAction $action): bool
    {
        $userRank = $this->member->rank;
        $newRank  = $action->rank;

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

        $name = strtolower($member->name);
        if (self::where('name', $name)->exists()) {
            $name = $name . '_' . $member->clan_id;
        }

        return self::create([
            'name'      => $name,
            'email'     => self::resolveUniqueEmail($email, $member),
            'member_id' => $member->id,
            'role'      => Role::MEMBER,
        ]);
    }

    public static function resolveUniqueEmail(?string $email, Member $member): string
    {
        if (! $email || self::where('email', $email)->exists()) {
            return $member->id . '@placeholder.local';
        }

        return $email;
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
}
