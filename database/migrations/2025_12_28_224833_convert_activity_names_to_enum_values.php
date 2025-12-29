<?php

use App\Enums\ActivityType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->nullable()->after('name');
        });

        $legacyNames = DB::table('activities')
            ->select('name')
            ->distinct()
            ->pluck('name');

        foreach ($legacyNames as $legacyName) {
            $enumValue = ActivityType::fromLegacyName($legacyName);

            if ($enumValue) {
                DB::table('activities')
                    ->where('name', $legacyName)
                    ->update(['type' => $enumValue->value]);
            }
        }

        DB::table('activities')->whereNull('type')->delete();

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->renameColumn('type', 'name');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->renameColumn('name', 'type');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->string('name')->after('type');
        });

        foreach (ActivityType::cases() as $case) {
            $legacyName = strtolower(str_replace('_', '_', $case->name));
            DB::table('activities')
                ->where('type', $case->value)
                ->update(['name' => $legacyName]);
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
