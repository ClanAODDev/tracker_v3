<?php

namespace App\Models;

use App\Enums\Ability;
use App\Enums\ActivityType;
use App\Enums\Rank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::created(function (Note $note) {
            $note->member?->recordActivity(ActivityType::CREATED_NOTE, [
                'type' => $note->type,
            ]);
        });

        static::updated(function (Note $note) {
            $note->member?->recordActivity(ActivityType::UPDATED_NOTE, [
                'type' => $note->type,
            ]);
        });

        static::deleted(function (Note $note) {
            $note->member?->recordActivity(ActivityType::DELETED_NOTE, [
                'type' => $note->type,
            ]);
        });
    }

    protected static array $noteTypes = [
        'misc'     => 'Misc',
        'negative' => 'Negative',
        'positive' => 'Positive',
    ];

    protected $fillable = [
        'body',
        'member_id',
        'author_id',
        'type',
    ];

    public static function allNoteTypes(): array
    {
        $types = static::$noteTypes;
        $user  = auth()->user();

        if (static::canManageSrLdr($user)) {
            $types['sr_ldr'] = 'Sr Leaders Only';
        }

        if (static::canManageMsgt($user)) {
            $types['msgt'] = 'MSGT+ Only';
        }

        return $types;
    }

    public static function canManageSrLdr(?User $user): bool
    {
        return $user?->can(Ability::UseSeniorLeaderNotes) ?? false;
    }

    public static function canManageMsgt(?User $user): bool
    {
        return $user?->can(Ability::UseMasterSergeantNotes) || ($user?->member?->isAtLeast(Rank::MASTER_SERGEANT) ?? false);
    }

    public static function bypassesNoteRestrictions(?User $user): bool
    {
        return $user !== null && ($user->can(Ability::ManageAllNotes) || $user->isDeveloper());
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        $hiddenTypes = array_keys(array_filter([
            'sr_ldr' => ! static::canManageSrLdr($user),
            'msgt'   => ! static::canManageMsgt($user),
        ]));

        $query->when($hiddenTypes !== [], fn (Builder $query) => $query->whereNotIn($query->qualifyColumn('type'), $hiddenTypes));

        if (static::bypassesNoteRestrictions($user)) {
            return $query;
        }

        $viewerRank = $user->member?->rank?->value;

        return $query->where(fn (Builder $query) => $query
            ->where($query->qualifyColumn('author_id'), $user->id)
            ->when($viewerRank !== null, fn (Builder $query) => $query->orWhereIn(
                $query->qualifyColumn('member_id'),
                Member::withTrashed()->where('rank', '<', $viewerRank)->select('id'),
            ))
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function leave(): HasOne
    {
        return $this->hasOne(Leave::class);
    }

    public function changed(): bool
    {
        return $this->updated_at !== $this->created_at;
    }
}
