<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $formerMembers = DB::table('members')
            ->where(fn ($query) => $query->where('division_id', 0)->orWhereNull('division_id'))
            ->select('id');

        $reset = DB::table('users')
            ->whereIn('member_id', $formerMembers)
            ->whereNotIn('role', [Role::MEMBER->value, Role::BANNED->value])
            ->update(['role' => Role::MEMBER->value]);

        Log::info("Reset {$reset} former members to the member role");
    }

    public function down(): void {}
};
