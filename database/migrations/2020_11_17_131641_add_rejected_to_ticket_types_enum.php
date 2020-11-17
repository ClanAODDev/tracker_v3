<?php

use Illuminate\Database\Migrations\Migration;

class AddRejectedToTicketTypesEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE tickets CHANGE COLUMN state state ENUM('new', 'assigned', 'resolved', 'rejected') NOT NULL DEFAULT 'new'");
    }
}
