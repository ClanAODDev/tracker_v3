<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DivisionUnitLevel extends Model
{
    protected $guarded = ['id'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    protected function label(): Attribute
    {
        return $this->titleCased();
    }

    protected function labelPlural(): Attribute
    {
        return $this->titleCased();
    }

    protected function leaderTitle(): Attribute
    {
        return $this->titleCased();
    }

    public function leaderAbbreviation(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->leader_title), -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) > 1) {
            return Str::upper(collect($words)->map(fn (string $word) => Str::substr($word, 0, 1))->implode(''));
        }

        return Str::upper(Str::substr($words[0] ?? '', 0, 3));
    }

    private function titleCased(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Str::title($value),
            set: fn (?string $value) => $value === null ? null : Str::title($value),
        );
    }

    public static function createDefaultsFor(Division $division): int
    {
        $created = 0;
        $now     = now();

        foreach ([1 => ['Platoon', 'Platoon Leader'], 2 => ['Squad', 'Squad Leader']] as $depth => [$label, $leader]) {
            $created += DB::table('division_unit_levels')->insertOrIgnore([
                'division_id'  => $division->id,
                'depth'        => $depth,
                'label'        => $label,
                'label_plural' => Str::plural($label),
                'leader_title' => $leader,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        return $created;
    }
}
