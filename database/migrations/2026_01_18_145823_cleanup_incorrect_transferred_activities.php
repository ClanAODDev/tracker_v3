<?php

use App\Enums\ActivityType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('activities')
            ->where('name', ActivityType::TRANSFERRED->value)
            ->where('properties', 'like', '%platoon%')
            ->whereNot('properties', 'like', '%to_division%')
            ->delete();
    }

    public function down(): void
    {
    }
};
