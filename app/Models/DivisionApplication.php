<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[Unguarded]
class DivisionApplication extends Model implements Commentable
{
    use HasComments;
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (DivisionApplication $application) {
            $application->comments()->delete();
        });
    }

    protected $casts = [
        'responses' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function handle(): BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }

    public function handleSummary(): ?array
    {
        if (! $this->handle_value) {
            return null;
        }

        return [
            'id'    => $this->handle_id,
            'label' => $this->handle?->label ?? 'Handle',
            'value' => $this->handle_value,
        ];
    }

    public function discordAvatarUrl(): ?string
    {
        if (! $this->discord_avatar || ! $this->user?->discord_id) {
            return null;
        }

        return sprintf(
            'https://cdn.discordapp.com/avatars/%s/%s.png?size=64',
            $this->user->discord_id,
            $this->discord_avatar
        );
    }
}
