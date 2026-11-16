<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_recruiter_id_index');
            $table->dropColumn(['recruiter_id', 'last_trained_by']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex(['leader_id']);
            $table->dropColumn('leader_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->integer('recruiter_id')->nullable()->index('members_recruiter_id_index');
            $table->integer('last_trained_by')->nullable();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->unsignedMediumInteger('leader_id')->nullable()->index();
        });

        DB::statement('UPDATE members x JOIN members m ON m.id = x.recruiter_member_id SET x.recruiter_id = m.clan_id');
        DB::statement('UPDATE members x JOIN members m ON m.id = x.trainer_member_id SET x.last_trained_by = m.clan_id');
        DB::statement('UPDATE units u JOIN members m ON m.id = u.leader_member_id SET u.leader_id = m.clan_id');
    }
};
