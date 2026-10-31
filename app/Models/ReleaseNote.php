<?php

namespace App\Models;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReleaseNote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $note) {
            if (blank($note->slug)) {
                $note->slug = static::uniqueSlug($note->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'release-note';
        $slug = $base;

        for ($suffix = 2; static::query()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    public function newer(): ?self
    {
        return static::published()
            ->where(fn (Builder $query) => $query
                ->where('published_at', '>', $this->published_at)
                ->orWhere(fn (Builder $query) => $query->where('published_at', $this->published_at)->where('id', '>', $this->id)))
            ->orderBy('published_at')
            ->orderBy('id')
            ->first();
    }

    public function older(): ?self
    {
        return static::published()
            ->where(fn (Builder $query) => $query
                ->where('published_at', '<', $this->published_at)
                ->orWhere(fn (Builder $query) => $query->where('published_at', $this->published_at)->where('id', '<', $this->id)))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function status(): string
    {
        return match (true) {
            $this->published_at === null    => 'draft',
            $this->published_at->isFuture() => 'scheduled',
            default                         => 'published',
        };
    }

    public function excerpt(int $limit = 240): string
    {
        $html = preg_replace('#<h([1-6])\b[^>]*>.*?</h\1>#is', ' ', (string) Markdown::convertToHtml((string) $this->body));
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), $limit);
    }
}
