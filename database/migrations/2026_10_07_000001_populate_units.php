<?php

use App\Services\Units\LegacyUnitSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyUnitSync::class)->sync();
    }

    public function down(): void {}
};
