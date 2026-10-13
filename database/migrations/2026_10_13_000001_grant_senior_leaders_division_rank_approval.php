<?php

use App\Enums\Ability;
use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('role_abilities')->insertOrIgnore([
            'role'       => Role::SENIOR_LEADER->value,
            'ability'    => Ability::ApproveDivisionRankActions->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('role_abilities')
            ->where('role', Role::SENIOR_LEADER->value)
            ->where('ability', Ability::ApproveDivisionRankActions->value)
            ->delete();
    }
};
