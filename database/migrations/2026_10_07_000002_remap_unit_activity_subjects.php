<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TYPES = [
        'App\Models\Platoon' => 'platoon',
        'App\Models\Squad'   => 'squad',
    ];

    public function up(): void
    {
        foreach (self::TYPES as $subjectType => $legacyType) {
            DB::table('activities as a')
                ->join('units as u', fn ($join) => $join->on('u.legacy_id', '=', 'a.subject_id')->where('u.legacy_type', $legacyType))
                ->where('a.subject_type', $subjectType)
                ->update(['a.subject_type' => 'App\Models\Unit', 'a.subject_id' => DB::raw('u.id')]);
        }
    }

    public function down(): void
    {
        foreach (self::TYPES as $subjectType => $legacyType) {
            DB::table('activities as a')
                ->join('units as u', 'u.id', '=', 'a.subject_id')
                ->where('a.subject_type', 'App\Models\Unit')
                ->where('u.legacy_type', $legacyType)
                ->update(['a.subject_type' => $subjectType, 'a.subject_id' => DB::raw('u.legacy_id')]);
        }
    }
};
