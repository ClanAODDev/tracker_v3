<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDivisionHandlePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop handle id since we're using many to many relationship
        if (Schema::hasColumn('divisions', 'handle_id')) {
            Schema::table('divisions', function ($table) {
                $table->dropColumn('handle_id');
            });
        }

        // drop type since we're using many to many relationship
        if (Schema::hasColumn('handles', 'type')) {
            Schema::table('handles', function ($table) {
                $table->dropColumn('type');
            });
        }

        // many divisions to many handles
        Schema::create('division_handle', function (Blueprint $table) {
            $table->integer('division_id')->unsigned()->index();
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->integer('handle_id')->unsigned()->index();
            $table->foreign('handle_id')->references('id')->on('handles')->onDelete('cascade');
            $table->primary(['division_id', 'handle_id']);
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
    }
}
