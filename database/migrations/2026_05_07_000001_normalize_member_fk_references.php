<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_reminders', function (Blueprint $table) {
            $table->dropForeignIfExists('activity_reminders_member_id_foreign');
            $table->unsignedInteger('member_id')->change();
        });

        DB::statement('
            UPDATE activity_reminders ar
            JOIN members m ON m.clan_id = ar.member_id
            SET ar.member_id = m.id
            WHERE ar.member_id != m.id
        ');

        DB::statement('
            UPDATE activity_reminders ar
            JOIN members m ON m.clan_id = ar.reminded_by_id
            SET ar.reminded_by_id = m.id
            WHERE ar.reminded_by_id != m.id
        ');

        DB::statement('
            UPDATE award_member am
            JOIN members m ON m.clan_id = am.member_id
            SET am.member_id = m.id
            WHERE am.member_id != m.id
        ');

        DB::statement('
            UPDATE leaves l
            JOIN members m ON m.clan_id = l.member_id
            SET l.member_id = m.id
            WHERE l.member_id != m.id
        ');

        DB::statement('
            UPDATE member_requests mr
            JOIN members m ON m.clan_id = mr.member_id
            SET mr.member_id = m.id
            WHERE mr.member_id != m.id
        ');

        DB::statement('
            UPDATE member_requests mr
            JOIN members m ON m.clan_id = mr.requester_id
            SET mr.requester_id = m.id
            WHERE mr.requester_id != m.id
        ');

        DB::statement('
            UPDATE member_requests mr
            JOIN members m ON m.clan_id = mr.approver_id
            SET mr.approver_id = m.id
            WHERE mr.approver_id != m.id
        ');

        DB::statement('
            UPDATE member_requests mr
            JOIN members m ON m.clan_id = mr.holder_id
            SET mr.holder_id = m.id
            WHERE mr.holder_id IS NOT NULL AND mr.holder_id != m.id
        ');

        DB::statement('
            UPDATE users u
            JOIN members m ON m.clan_id = u.member_id
            SET u.member_id = m.id
            WHERE u.member_id != m.id
        ');

        Schema::table('activity_reminders', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_reminders', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedMediumInteger('member_id')->change();
        });

        Schema::table('activity_reminders', function (Blueprint $table) {
            $table->foreign('member_id')->references('clan_id')->on('members')->cascadeOnDelete();
        });
    }
};
