<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONVERSIONS = [
        ['table' => 'members', 'from' => 'recruiter_id', 'to' => 'recruiter_member_id'],
        ['table' => 'members', 'from' => 'last_trained_by', 'to' => 'trainer_member_id'],
        ['table' => 'units', 'from' => 'leader_id', 'to' => 'leader_member_id'],
    ];

    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('recruiter_member_id')->nullable()->after('recruiter_id');
            $table->unsignedInteger('trainer_member_id')->nullable()->after('last_trained_by');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('leader_member_id')->nullable()->after('leader_id');
        });

        $this->convert();

        Schema::table('members', function (Blueprint $table) {
            $table->foreign('recruiter_member_id')->references('id')->on('members')->nullOnDelete();
            $table->foreign('trainer_member_id')->references('id')->on('members')->nullOnDelete();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->foreign('leader_member_id')->references('id')->on('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['leader_member_id']);
            $table->dropColumn('leader_member_id');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['recruiter_member_id']);
            $table->dropForeign(['trainer_member_id']);
            $table->dropColumn(['recruiter_member_id', 'trainer_member_id']);
        });
    }

    public function convert(): void
    {
        foreach (self::CONVERSIONS as ['table' => $table, 'from' => $from, 'to' => $to]) {
            $expected = $this->convertibleCount($table, $from);

            DB::statement("
                UPDATE {$table} x
                JOIN members m ON m.clan_id = x.{$from}
                SET x.{$to} = m.id
                WHERE x.{$to} IS NULL AND x.{$from} IS NOT NULL AND x.{$from} <> 0
            ");

            $converted = DB::table($table)->whereNotNull($to)->count();

            if ($converted !== $expected) {
                throw new RuntimeException("{$table}.{$from}: expected {$expected} converted rows, got {$converted}.");
            }
        }
    }

    private function convertibleCount(string $table, string $column): int
    {
        return DB::table("{$table} as x")
            ->whereNotNull("x.{$column}")
            ->where("x.{$column}", '<>', 0)
            ->whereExists(fn ($query) => $query->from('members as m')->whereColumn('m.clan_id', "x.{$column}"))
            ->count();
    }
};
