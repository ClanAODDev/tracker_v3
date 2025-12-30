<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Award extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'allow_request' => 'boolean',
        'repeatable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $award) {
            $award->recipients->each->delete();
        });
    }

    public function recipients()
    {
        return $this->hasMany(MemberAward::class, 'award_id', 'id')
            ->where('approved', '=', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->with('member');
    }

    public function unapprovedRecipients()
    {
        return $this->hasMany(MemberAward::class, 'award_id', 'id')
            ->where('approved', '=', false)
            ->with('member');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->select('id', 'name', 'slug', 'active', 'logo');
    }

    public function prerequisite()
    {
        return $this->belongsTo(Award::class, 'prerequisite_award_id');
    }

    public function dependents()
    {
        return $this->hasMany(Award::class, 'prerequisite_award_id');
    }

    public function getPrerequisiteChain(): array
    {
        $chain = [];
        $current = $this->prerequisite;

        while ($current) {
            $chain[] = $current;
            $current = $current->prerequisite;
        }

        return $chain;
    }

    public function isPartOfTieredGroup(): bool
    {
        return $this->prerequisite_award_id !== null || $this->dependents()->exists();
    }

    public function getRarity(?int $count = null): string
    {
        $count ??= $this->recipients_count ?? $this->recipients()->count();

        foreach (config('aod.awards.rarity') as $key => $thresholds) {
            if ($count >= $thresholds['min'] && ($thresholds['max'] === null || $count <= $thresholds['max'])) {
                return $key;
            }
        }

        return 'common';
    }

    public function getImagePath(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset(Storage::url($this->image));
        }

        return asset(config('aod.logo'));
    }

    public function getTieredGroupSlug(): ?string
    {
        if (! $this->isPartOfTieredGroup()) {
            return null;
        }

        $chain = collect([$this])->merge($this->getPrerequisiteChain());

        $current = $this;
        while ($dependent = self::where('prerequisite_award_id', $current->id)->first()) {
            $chain->push($dependent);
            $current = $dependent;
        }

        $baseTier = $chain->sortBy('display_order')->first();

        if ($baseTier->tiered_group_name) {
            return \Illuminate\Support\Str::slug($baseTier->tiered_group_name);
        }

        $names = $chain->pluck('name')->toArray();

        if (collect($names)->contains(fn ($n) => str_contains($n, 'Years of Service'))) {
            return 'aod-tenure';
        }

        $commonWords = [];
        $firstWords = explode(' ', $names[0]);
        foreach ($firstWords as $word) {
            if (collect($names)->every(fn ($n) => str_contains($n, $word))) {
                $commonWords[] = $word;
            }
        }

        $groupName = ! empty($commonWords) ? implode(' ', $commonWords) : $chain->first()->name . ' Series';

        return \Illuminate\Support\Str::slug($groupName);
    }
}
