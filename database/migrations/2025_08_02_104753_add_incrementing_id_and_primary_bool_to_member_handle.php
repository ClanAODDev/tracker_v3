<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('handle_member', function (Blueprint $table) {
            $table->dropPrimary();
            $table->boolean('primary')
                ->default(true) // default to true since there will only be one primary handle per type
                ->after('handle_id')
                ->comment('Indicates if this is the primary handle for the handle type');
            $table->increments('id')->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
