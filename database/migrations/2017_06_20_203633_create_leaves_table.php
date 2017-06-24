<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->index();
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->integer('approver_id')->unsigned()->index();
            $table->integer('requester_id')->unsigned()->index();
            $table->enum('reason', ['military', 'education', 'emergency', 'personal']);
            $table->integer('note_id')->unsigned();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('extended')->nullable();
            $table->timestamps();

            // member
            // approver
            // requester
            // note
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
