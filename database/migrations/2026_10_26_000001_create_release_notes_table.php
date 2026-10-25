<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_notes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('version', 50)->nullable();
            $table->longText('body');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        $legacy = resource_path('views/changelog.md');

        if (is_file($legacy)) {
            DB::table('release_notes')->insert([
                'title'        => 'Earlier changes',
                'version'      => '3.7.2',
                'body'         => file_get_contents($legacy),
                'published_at' => '2024-04-01 00:00:00',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('release_notes');
    }
};
