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
            'role'       => Role::ADMIN->value,
            'ability'    => Ability::GrantClanWideAwards->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('role_abilities')
            ->where('ability', Ability::GrantClanWideAwards->value)
            ->delete();
    }
};
