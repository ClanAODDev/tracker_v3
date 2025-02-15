<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $neededColumns = [
            'justification' => 'text',
            'requester_id' => 'integer',
            'approved_at' => 'dateTime',
            'accepted_at' => 'dateTime',
            'declined_at' => 'dateTime',
        ];

        if (! Schema::hasColumns('rank_actions', $neededColumns)) {
            Schema::table('rank_actions', function (Blueprint $table) use ($neededColumns) {
                foreach ($neededColumns as $column => $type) {
                    $table->$type($column)->nullable();
                }
            });

            DB::table('rank_actions')->update([
                'approved_at' => DB::raw('created_at'),
                'accepted_at' => DB::raw('created_at'),
            ]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
