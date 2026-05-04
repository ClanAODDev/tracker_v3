<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transfers')
            ->whereNull('approved_at')
            ->whereIn('member_id', function ($query) {
                $query->select('id')
                    ->from('members')
                    ->where('division_id', 0);
            })
            ->delete();
    }

    public function down(): void {}
};
