<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\Rank;
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
        return $user?->isRole(['admin', 'sr_ldr']) ?? false;
    }

    public static function canManageMsgt(?User $user): bool
    {
        return $user?->isRole('admin') || ($user?->member?->isAtLeast(Rank::MASTER_SERGEANT) ?? false);
    }

    public static function isTypeVisibleTo(string $type, ?User $user): bool
    {
        return match ($type) {
            'sr_ldr' => static::canManageSrLdr($user),
            'msgt'   => static::canManageMsgt($user),
            default  => true,
        };
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
