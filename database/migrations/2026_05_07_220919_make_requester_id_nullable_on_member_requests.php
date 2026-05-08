<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_requests', function (Blueprint $table) {
            $table->unsignedInteger('requester_id')->nullable()->change();
        });

        DB::statement('
            UPDATE member_requests mr
            JOIN members m ON m.clan_id = mr.requester_id
            SET mr.requester_id = m.id
            WHERE mr.requester_id != m.id
        ');

        DB::table('member_requests')
            ->whereNotNull('requester_id')
            ->whereNotIn('requester_id', DB::table('members')->select('id'))
            ->update(['requester_id' => null]);

        DB::statement('
            UPDATE rank_actions ra
            JOIN members m ON m.clan_id = ra.requester_id
            SET ra.requester_id = m.id
            WHERE ra.requester_id IS NOT NULL
              AND ra.requester_id != m.id
        ');
    }

    public function down(): void
    {
        Schema::table('member_requests', function (Blueprint $table) {
            $table->unsignedInteger('requester_id')->nullable(false)->change();
        });
    }
};
