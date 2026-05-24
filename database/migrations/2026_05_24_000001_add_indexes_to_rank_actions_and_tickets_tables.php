<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rank_actions', function (Blueprint $table) {
            $table->index('member_id');
            $table->index('approver_id');
            $table->index('requester_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('state');
            $table->index('caller_id');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('rank_actions', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
            $table->dropIndex(['approver_id']);
            $table->dropIndex(['requester_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['state']);
            $table->dropIndex(['caller_id']);
            $table->dropIndex(['owner_id']);
        });
    }
};
