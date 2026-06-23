<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leaves')
            ->whereIn('member_id', function ($q) {
                $q->select('members.id')
                    ->from('members')
                    ->leftJoin('divisions', 'members.division_id', '=', 'divisions.id')
                    ->where(function ($inner) {
                        $inner->whereNull('members.division_id')
                            ->orWhere('members.division_id', 0)
                            ->orWhere('divisions.active', false)
                            ->orWhereNotNull('divisions.shutdown_at');
                    });
            })
            ->delete();
    }

    public function down(): void {}
};
