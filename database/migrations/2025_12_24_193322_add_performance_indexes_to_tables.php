<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->index('division_id');
            $table->index('platoon_id');
            $table->index('squad_id');
            $table->index('recruiter_id');
            $table->index('last_voice_activity');
            $table->index('join_date');
            $table->index(['division_id', 'join_date']);
            $table->index(['division_id', 'last_voice_activity']);
        });

        Schema::table('censuses', function (Blueprint $table) {
            $table->index('division_id');
            $table->index(['division_id', 'created_at']);
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->index('member_id');
            $table->index('division_id');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->index('member_id');
            $table->index('author_id');
        });

        Schema::table('award_member', function (Blueprint $table) {
            $table->index('award_id');
            $table->index('member_id');
        });

        Schema::table('platoons', function (Blueprint $table) {
            $table->index('division_id');
        });

        Schema::table('squads', function (Blueprint $table) {
            $table->index('platoon_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['division_id']);
            $table->dropIndex(['platoon_id']);
            $table->dropIndex(['squad_id']);
            $table->dropIndex(['recruiter_id']);
            $table->dropIndex(['last_voice_activity']);
            $table->dropIndex(['join_date']);
            $table->dropIndex(['division_id', 'join_date']);
            $table->dropIndex(['division_id', 'last_voice_activity']);
        });

        Schema::table('censuses', function (Blueprint $table) {
            $table->dropIndex(['division_id']);
            $table->dropIndex(['division_id', 'created_at']);
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
            $table->dropIndex(['division_id']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
            $table->dropIndex(['author_id']);
        });

        Schema::table('award_member', function (Blueprint $table) {
            $table->dropIndex(['award_id']);
            $table->dropIndex(['member_id']);
        });

        Schema::table('platoons', function (Blueprint $table) {
            $table->dropIndex(['division_id']);
        });

        Schema::table('squads', function (Blueprint $table) {
            $table->dropIndex(['platoon_id']);
        });
    }
};
