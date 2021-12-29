<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowPmColumnToMembersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table
                ->boolean('allow_pm')
                ->after('privacy_flag')
                ->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
