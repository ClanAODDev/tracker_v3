<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-reactions.table', 'model_reactions'), function (Blueprint $table) {
            $table->id();
            $table->morphs('reactable');
            $table->morphs('reactor');

            if (config('database.default') === 'mysql') {
                $table->string('reaction', 50)->collation('utf8mb4_bin');
            } else {
                $table->string('reaction', 50);
            }

            $table->timestamps();

            $table->unique(
                ['reactable_type', 'reactable_id', 'reactor_type', 'reactor_id', 'reaction'],
                'model_reactions_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-reactions.table', 'model_reactions'));
    }
};
