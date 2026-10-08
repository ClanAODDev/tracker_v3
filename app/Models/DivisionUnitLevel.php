<?php

namespace App\Models;

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

    public static function createDefaultsFor(Division $division): int
    {
        $created = 0;
        $now     = now();

        foreach ([1 => ['platoon', 'platoon leader'], 2 => ['squad', 'squad leader']] as $depth => [$unit, $leader]) {
            $label = $division->locality($unit);

            $created += DB::table('division_unit_levels')->insertOrIgnore([
                'division_id'  => $division->id,
                'depth'        => $depth,
                'label'        => $label,
                'label_plural' => Str::plural($label),
                'leader_title' => $division->locality($leader),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        return $created;
    }
}
