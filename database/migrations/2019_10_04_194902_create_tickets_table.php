<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('state', ['new', 'assigned', 'resolved']);
            $table->unsignedInteger('type_id')->default(1);
            $table->longText('description');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('owner_id')->nullable();
            $table->unsignedInteger('division_id');
            $table->dateTime('resolved_at')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
