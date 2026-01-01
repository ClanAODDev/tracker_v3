<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role_id', 6)->update(['role_id' => 5]);
        DB::table('users')->where('role_id', 5)->update(['role_id' => 4]);
        DB::table('users')->where('role_id', 4)->update(['role_id' => 3]);

        DB::table('divisions')->where('officer_role_id', 6)->update(['officer_role_id' => 5]);
        DB::table('divisions')->where('officer_role_id', 5)->update(['officer_role_id' => 4]);
        DB::table('divisions')->where('officer_role_id', 4)->update(['officer_role_id' => 3]);
    }

    public function down(): void
    {
        DB::table('users')->where('role_id', 3)->update(['role_id' => 4]);
        DB::table('users')->where('role_id', 4)->update(['role_id' => 5]);
        DB::table('users')->where('role_id', 5)->update(['role_id' => 6]);

        DB::table('divisions')->where('officer_role_id', 3)->update(['officer_role_id' => 4]);
        DB::table('divisions')->where('officer_role_id', 4)->update(['officer_role_id' => 5]);
        DB::table('divisions')->where('officer_role_id', 5)->update(['officer_role_id' => 6]);
    }
};
