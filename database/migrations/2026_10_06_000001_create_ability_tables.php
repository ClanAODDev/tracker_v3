<?php

use App\Authorization\CodeAbilityMap;
use App\Authorization\ForumRoleSource;
use App\Enums\Ability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_abilities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('role');
            $table->string('ability', 64);
            $table->timestamps();

            $table->unique(['role', 'ability']);
        });

        Schema::create('user_abilities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('ability', 64);
            $table->unsignedInteger('granted_by')->nullable();
            $table->string('reason');
            $table->timestamps();

            $table->unique(['user_id', 'ability']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('ability_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('action', 32);
            $table->unsignedTinyInteger('role')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ability', 64)->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        $defaults = new CodeAbilityMap(new ForumRoleSource);
        $now      = now();

        DB::table('role_abilities')->insert(collect(Ability::cases())
            ->flatMap(fn (Ability $ability) => collect($defaults->rolesFor($ability))->map(fn ($role) => [
                'role'       => $role->value,
                'ability'    => $ability->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]))
            ->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('ability_audits');
        Schema::dropIfExists('user_abilities');
        Schema::dropIfExists('role_abilities');
    }
};
