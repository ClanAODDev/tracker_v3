<?php

namespace App\Models;

use App\Enums\ActivityType;
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
        if (auth()->user()->isRole(['admin', 'sr_ldr'])) {
            static::$noteTypes['sr_ldr'] = 'Sr Leaders Only';
        }

        return static::$noteTypes;
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
