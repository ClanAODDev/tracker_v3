<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('divisions')
            ->whereNotNull('settings')
            ->update(['settings' => DB::raw("JSON_REMOVE(settings, '$.locality')")]);
    }
};
