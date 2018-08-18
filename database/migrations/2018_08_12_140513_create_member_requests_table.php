<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('requester_id');
            $table->unsignedInteger('member_id');
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('approver_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('members', 'pending_member')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('pending_member');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('member_requests');
    }
}
