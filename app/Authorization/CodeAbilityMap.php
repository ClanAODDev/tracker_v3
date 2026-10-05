<?php

namespace App\Authorization;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\User;

class CodeAbilityMap implements AbilityMap
{
    public function __construct(private readonly RoleSource $roles) {}

    public function allows(User $user, Ability $ability): bool
    {
        $role = $ability->resolvesAgainstStoredRole()
            ? $this->roles->storedRole($user)
            : $this->roles->effectiveRole($user);

        return $role !== null && in_array($role, $this->rolesFor($ability), true);
    }

    public function rolesFor(Ability $ability): array
    {
        $admin   = Role::ADMIN;
        $senior  = Role::SENIOR_LEADER;
        $officer = Role::OFFICER;
        $banned  = Role::BANNED;

        return match ($ability) {
            Ability::AccessAdminPanel,
            Ability::ViewAdminPages,
            Ability::ImpersonateRoles,
            Ability::ImpersonateUsers,
            Ability::ManageUsers,
            Ability::ManageApiTokens,
            Ability::ManageDivisions,
            Ability::ActAcrossDivisions,
            Ability::ManageAllUnits,
            Ability::ManageAllMembers,
            Ability::ManageAllTransfers,
            Ability::ManageAllMemberRequests,
            Ability::ManageAllNotes,
            Ability::UseMasterSergeantNotes,
            Ability::ManageAllRankActions,
            Ability::OverrideRankActionRules,
            Ability::DemoteMembers,
            Ability::AutoApproveJuniorPromotions,
            Ability::ManageGlobalTags,
            Ability::ManageAllTickets => [$admin],

            Ability::SeeDivisionHealthAlerts => [$senior],

            Ability::PromoteMembers => [$admin, $officer],

            Ability::UseAdvancedApiScopes,
            Ability::ViewDivisionSettings,
            Ability::ManageUnits,
            Ability::ManageMembers,
            Ability::ClearActivityReminders,
            Ability::SeparateMembers,
            Ability::ViewAllActivity,
            Ability::ManageUnassignedMembers,
            Ability::ConductTraining,
            Ability::TransferMembers,
            Ability::ManageMemberRequests,
            Ability::DeleteApplications,
            Ability::EditLeaves,
            Ability::EditAnyNote,
            Ability::UseSeniorLeaderNotes,
            Ability::ManageMemberAwards,
            Ability::ApproveAwards,
            Ability::ManageDivisionTags,
            Ability::UseSeniorLeaderTags => [$admin, $senior],

            Ability::AccessModPanel,
            Ability::UseHelpDesk,
            Ability::ViewRecruitingGuide,
            Ability::PreviewApplicationForm,
            Ability::UseBulkMode,
            Ability::ViewUnits,
            Ability::ManageDivisionMembers,
            Ability::RemindInactiveMembers,
            Ability::ManagePartTimers,
            Ability::ViewTransfers,
            Ability::ViewRankActions,
            Ability::RequestAwards,
            Ability::AssignTags,
            Ability::UseOfficerTags => [$admin, $senior, $officer],

            Ability::Recruit,
            Ability::ViewMemberHistory,
            Ability::ViewDivisionActivity,
            Ability::ViewLeaves,
            Ability::CreateLeaves,
            Ability::ViewNotes,
            Ability::CreateNotes => [$admin, $senior, $officer, $banned],
        };
    }
}
