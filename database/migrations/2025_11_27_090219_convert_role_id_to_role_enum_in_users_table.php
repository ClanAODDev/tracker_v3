<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->after('email')->nullable();
        });

        DB::table('users')->get()->each(function ($user) {
            $role = Role::fromId($user->role_id ?? 1);
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => $role->value]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->change();

            if (DB::getSchemaBuilder()->hasColumn('users', 'role_id')) {
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'users'
                     AND COLUMN_NAME = 'role_id'
                     AND REFERENCED_TABLE_NAME IS NOT NULL"
                );

                if (!empty($foreignKeys)) {
                    $table->dropForeign(['role_id']);
                }

                $table->dropColumn('role_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('email');
        });

        DB::table('users')->get()->each(function ($user) {
            $roleEnum = Role::from($user->role);
            $roleId = match ($roleEnum) {
                Role::MEMBER => 1,
                Role::OFFICER => 2,
                Role::JUNIOR_LEADER => 3,
                Role::SENIOR_LEADER => 4,
                Role::ADMIN => 5,
                Role::BANNED => 6,
            };

            DB::table('users')
                ->where('id', $user->id)
                ->update(['role_id' => $roleId]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->dropColumn('role');
        });
    }
};
