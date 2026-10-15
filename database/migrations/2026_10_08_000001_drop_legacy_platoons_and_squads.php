<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_platoon_id_index');
            $table->dropIndex('members_squad_id_index');
            $table->dropColumn(['platoon_id', 'squad_id']);
        });

        Schema::dropIfExists('squads');
        Schema::dropIfExists('platoons');
    }

    public function down(): void {}
};
