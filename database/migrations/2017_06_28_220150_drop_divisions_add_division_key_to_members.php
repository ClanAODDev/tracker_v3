<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDivisionsAddDivisionKeyToMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop manyToMany for division tracking
        Schema::dropIfExists('division_member');

        // add primary key for divisions to members
        Schema::table('members', function (Blueprint $table) {
            $table->integer('division_id')->after('position_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
