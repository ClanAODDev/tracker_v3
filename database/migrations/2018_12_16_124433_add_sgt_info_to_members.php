<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSgtInfoToMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //last_trained_at
        //last_trained_by
        //xo_at
        //co_at
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('last_trained_at')->after('last_promoted')->nullable();
            $table->integer('last_trained_by')->after('last_trained_at')->nullable();
            $table->timestamp('xo_at')->after('last_trained_by')->nullable();
            $table->timestamp('co_at')->after('xo_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // omit
    }
}
