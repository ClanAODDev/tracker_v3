<?php

use App\Enums\Ability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const string REASON = 'Carried over from the developer flag';

    private const array ABILITIES = [
        Ability::AccessAdminPanel,
        Ability::AccessModPanel,
        Ability::ViewLogs,
        Ability::ImpersonateRoles,
        Ability::ManageUsers,
        Ability::ManageApiTokens,
        Ability::ManageDivisions,
        Ability::DeleteDivisions,
        Ability::ManageAllUnits,
        Ability::ManageAllMembers,
        Ability::ManageAllTransfers,
        Ability::ManageAllMemberRequests,
        Ability::ManageAllNotes,
        Ability::UseBulkMode,
        Ability::DeleteAwards,
    ];

    public function up(): void
    {
        $now = now();

        foreach (DB::table('users')->where('developer', true)->get(['id', 'role']) as $user) {
            $fromRole = DB::table('role_abilities')->where('role', $user->role)->pluck('ability')->flip();

            foreach (self::ABILITIES as $ability) {
                if ($fromRole->has($ability->value)) {
                    continue;
                }

                $inserted = DB::table('user_abilities')->insertOrIgnore([
                    'user_id'    => $user->id,
                    'ability'    => $ability->value,
                    'reason'     => self::REASON,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($inserted) {
                    DB::table('ability_audits')->insert([
                        'action'     => 'user_granted',
                        'user_id'    => $user->id,
                        'ability'    => $ability->value,
                        'reason'     => self::REASON,
                        'created_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('user_abilities')->where('reason', self::REASON)->delete();
        DB::table('ability_audits')->where('reason', self::REASON)->delete();
    }
};
