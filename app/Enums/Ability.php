<?php

namespace App\Enums;

enum Ability: string
{
    case AccessModPanel         = 'ability:access_mod_panel';
    case AccessAdminPanel       = 'ability:access_admin_panel';
    case ViewAdminPages         = 'ability:view_admin_pages';
    case UseHelpDesk            = 'ability:use_help_desk';
    case ViewRecruitingGuide    = 'ability:view_recruiting_guide';
    case PreviewApplicationForm = 'ability:preview_application_form';
    case UseBulkMode            = 'ability:use_bulk_mode';
    case ImpersonateRoles       = 'ability:impersonate_roles';
    case ImpersonateUsers       = 'ability:impersonate_users';
    case ManageUsers            = 'ability:manage_users';
    case ManageApiTokens        = 'ability:manage_api_tokens';
    case UseAdvancedApiScopes   = 'ability:use_advanced_api_scopes';

    case ManageDivisions         = 'ability:manage_divisions';
    case ViewDivisionSettings    = 'ability:view_division_settings';
    case SeeDivisionHealthAlerts = 'ability:see_division_health_alerts';
    case ActAcrossDivisions      = 'ability:act_across_divisions';
    case ManageAllUnits          = 'ability:manage_all_units';
    case ViewUnits               = 'ability:view_units';
    case ManageUnits             = 'ability:manage_units';

    case ManageAllMembers        = 'ability:manage_all_members';
    case Recruit                 = 'ability:recruit';
    case ManageMembers           = 'ability:manage_members';
    case ManageDivisionMembers   = 'ability:manage_division_members';
    case RemindInactiveMembers   = 'ability:remind_inactive_members';
    case ManagePartTimers        = 'ability:manage_part_timers';
    case ClearActivityReminders  = 'ability:clear_activity_reminders';
    case SeparateMembers         = 'ability:separate_members';
    case PromoteMembers          = 'ability:promote_members';
    case ViewMemberHistory       = 'ability:view_member_history';
    case ViewDivisionActivity    = 'ability:view_division_activity';
    case ViewAllActivity         = 'ability:view_all_activity';
    case ManageUnassignedMembers = 'ability:manage_unassigned_members';
    case ConductTraining         = 'ability:conduct_training';
    case ManageAllTransfers      = 'ability:manage_all_transfers';
    case ViewTransfers           = 'ability:view_transfers';
    case TransferMembers         = 'ability:transfer_members';
    case ManageAllMemberRequests = 'ability:manage_all_member_requests';
    case ManageMemberRequests    = 'ability:manage_member_requests';
    case DeleteApplications      = 'ability:delete_applications';

    case ViewLeaves             = 'ability:view_leaves';
    case CreateLeaves           = 'ability:create_leaves';
    case EditLeaves             = 'ability:edit_leaves';
    case ManageAllNotes         = 'ability:manage_all_notes';
    case ViewNotes              = 'ability:view_notes';
    case CreateNotes            = 'ability:create_notes';
    case EditAnyNote            = 'ability:edit_any_note';
    case UseSeniorLeaderNotes   = 'ability:use_senior_leader_notes';
    case UseMasterSergeantNotes = 'ability:use_master_sergeant_notes';

    case ViewRankActions             = 'ability:view_rank_actions';
    case ManageAllRankActions        = 'ability:manage_all_rank_actions';
    case OverrideRankActionRules     = 'ability:override_rank_action_rules';
    case DemoteMembers               = 'ability:demote_members';
    case AutoApproveJuniorPromotions = 'ability:auto_approve_junior_promotions';
    case ManageMemberAwards          = 'ability:manage_member_awards';
    case ApproveAwards               = 'ability:approve_awards';
    case RequestAwards               = 'ability:request_awards';

    case ManageGlobalTags    = 'ability:manage_global_tags';
    case ManageDivisionTags  = 'ability:manage_division_tags';
    case AssignTags          = 'ability:assign_tags';
    case UseSeniorLeaderTags = 'ability:use_senior_leader_tags';
    case UseOfficerTags      = 'ability:use_officer_tags';
    case ManageAllTickets    = 'ability:manage_all_tickets';

    public function resolvesAgainstStoredRole(): bool
    {
        return in_array($this, [
            self::Recruit,
            self::ConductTraining,
            self::UseAdvancedApiScopes,
        ], true);
    }
}
