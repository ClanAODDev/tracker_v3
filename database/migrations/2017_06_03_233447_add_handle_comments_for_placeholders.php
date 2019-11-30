<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddHandleCommentsForPlaceholders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('handles', function ($table) {
            $table->string('comments')->after('name')->nullable();
            $table->string('type')->after('name');
            $table->renameColumn('name', 'label');
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
