<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id')->unsigned();
            $table->integer('admin_id')->unsigned();
            $table->dateTime('decided_at')->nullable();
            $table->dateTime('effective_at')->nullable();
            $table->enum('decision', ['approved', 'denied']);
            $table->enum('type', ['promotion', 'demotion']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recommendations');
    }
};
