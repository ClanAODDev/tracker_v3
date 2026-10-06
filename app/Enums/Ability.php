<?php

namespace App\Enums;

use Illuminate\Support\Str;

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

    case ManagePermissions = 'ability:manage_permissions';

    public function label(): string
    {
        return Str::headline($this->name);
    }

    public function area(): string
    {
        return match ($this) {
            self::AccessModPanel              => 'Access',
            self::AccessAdminPanel            => 'Access',
            self::ViewAdminPages              => 'Access',
            self::UseHelpDesk                 => 'Access',
            self::ViewRecruitingGuide         => 'Access',
            self::PreviewApplicationForm      => 'Access',
            self::UseBulkMode                 => 'Access',
            self::ImpersonateRoles            => 'Access',
            self::ImpersonateUsers            => 'Access',
            self::ManageUsers                 => 'Access',
            self::ManageApiTokens             => 'Access',
            self::UseAdvancedApiScopes        => 'Access',
            self::ManageDivisions             => 'Divisions and units',
            self::ViewDivisionSettings        => 'Divisions and units',
            self::SeeDivisionHealthAlerts     => 'Divisions and units',
            self::ActAcrossDivisions          => 'Divisions and units',
            self::ManageAllUnits              => 'Divisions and units',
            self::ViewUnits                   => 'Divisions and units',
            self::ManageUnits                 => 'Divisions and units',
            self::ManageAllMembers            => 'Members',
            self::Recruit                     => 'Members',
            self::ManageMembers               => 'Members',
            self::ManageDivisionMembers       => 'Members',
            self::RemindInactiveMembers       => 'Members',
            self::ManagePartTimers            => 'Members',
            self::ClearActivityReminders      => 'Members',
            self::SeparateMembers             => 'Members',
            self::PromoteMembers              => 'Members',
            self::ViewMemberHistory           => 'Members',
            self::ViewDivisionActivity        => 'Members',
            self::ViewAllActivity             => 'Members',
            self::ManageUnassignedMembers     => 'Members',
            self::ConductTraining             => 'Members',
            self::ManageAllTransfers          => 'Members',
            self::ViewTransfers               => 'Members',
            self::TransferMembers             => 'Members',
            self::ManageAllMemberRequests     => 'Members',
            self::ManageMemberRequests        => 'Members',
            self::DeleteApplications          => 'Members',
            self::ViewLeaves                  => 'Leaves and notes',
            self::CreateLeaves                => 'Leaves and notes',
            self::EditLeaves                  => 'Leaves and notes',
            self::ManageAllNotes              => 'Leaves and notes',
            self::ViewNotes                   => 'Leaves and notes',
            self::CreateNotes                 => 'Leaves and notes',
            self::EditAnyNote                 => 'Leaves and notes',
            self::UseSeniorLeaderNotes        => 'Leaves and notes',
            self::UseMasterSergeantNotes      => 'Leaves and notes',
            self::ViewRankActions             => 'Rank actions and awards',
            self::ManageAllRankActions        => 'Rank actions and awards',
            self::OverrideRankActionRules     => 'Rank actions and awards',
            self::DemoteMembers               => 'Rank actions and awards',
            self::AutoApproveJuniorPromotions => 'Rank actions and awards',
            self::ManageMemberAwards          => 'Rank actions and awards',
            self::ApproveAwards               => 'Rank actions and awards',
            self::RequestAwards               => 'Rank actions and awards',
            self::ManageGlobalTags            => 'Tags and tickets',
            self::ManageDivisionTags          => 'Tags and tickets',
            self::AssignTags                  => 'Tags and tickets',
            self::UseSeniorLeaderTags         => 'Tags and tickets',
            self::UseOfficerTags              => 'Tags and tickets',
            self::ManageAllTickets            => 'Tags and tickets',
            self::ManagePermissions           => 'Permissions',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AccessModPanel              => 'Open the operations (mod) panel',
            self::AccessAdminPanel            => 'Open the admin panel',
            self::ViewAdminPages              => 'See admin-only tracker pages (division turnover, contributor docs)',
            self::UseHelpDesk                 => 'See the Get Help link in the navigation',
            self::ViewRecruitingGuide         => 'See the recruiting documentation',
            self::PreviewApplicationForm      => 'Preview a division\'s Discord application form',
            self::UseBulkMode                 => 'Use bulk selection on member tables',
            self::ImpersonateRoles            => 'View the site as another role',
            self::ImpersonateUsers            => 'Log in as another user',
            self::ManageUsers                 => 'Full control of user accounts',
            self::ManageApiTokens             => 'Create and revoke API tokens',
            self::UseAdvancedApiScopes        => 'Give API tokens division write and advanced read scopes',
            self::ManageDivisions             => 'Create divisions and edit any division\'s settings',
            self::ViewDivisionSettings        => 'See the division settings link in the operations panel',
            self::SeeDivisionHealthAlerts     => 'See outstanding inactive and voice issue alerts',
            self::ActAcrossDivisions          => 'Work with every division\'s records instead of only your own',
            self::ManageAllUnits              => 'Full control of every platoon and squad',
            self::ViewUnits                   => 'View platoons and squads in the operations panel',
            self::ManageUnits                 => 'Create, edit, and delete platoons and squads',
            self::ManageAllMembers            => 'Full control of every member record',
            self::Recruit                     => 'Recruit new members',
            self::ManageMembers               => 'Edit any member\'s details, assignments, leave, handles, and fields',
            self::ManageDivisionMembers       => 'Edit handles and fields for members of your own division',
            self::RemindInactiveMembers       => 'Flag inactive members and send activity reminders',
            self::ManagePartTimers            => 'Manage part-time division assignments',
            self::ClearActivityReminders      => 'Clear a member\'s activity reminder history',
            self::SeparateMembers             => 'Separate members from the clan',
            self::PromoteMembers              => 'Promote lower-ranked members in your division',
            self::ViewMemberHistory           => 'See a member\'s full history and reminders',
            self::ViewDivisionActivity        => 'See recent activity on the division page',
            self::ViewAllActivity             => 'See the full activity log',
            self::ManageUnassignedMembers     => 'Assign members without a platoon or squad',
            self::ConductTraining             => 'Record training completion',
            self::ManageAllTransfers          => 'Full control of transfers, including deletion',
            self::ViewTransfers               => 'View transfer requests',
            self::TransferMembers             => 'Transfer members between divisions in bulk',
            self::ManageAllMemberRequests     => 'Full control of member requests',
            self::ManageMemberRequests        => 'Approve, cancel, and delete member requests',
            self::DeleteApplications          => 'Delete division applications',
            self::ViewLeaves                  => 'View leaves of absence',
            self::CreateLeaves                => 'Create leaves of absence',
            self::EditLeaves                  => 'Edit and delete leaves of absence',
            self::ManageAllNotes              => 'Full control of every note',
            self::ViewNotes                   => 'View member notes',
            self::CreateNotes                 => 'Write member notes',
            self::EditAnyNote                 => 'Edit notes written by others',
            self::UseSeniorLeaderNotes        => 'Read and write senior-leader-only notes',
            self::UseMasterSergeantNotes      => 'Read and write MSgt+ notes regardless of rank',
            self::ViewRankActions             => 'View rank actions',
            self::ManageAllRankActions        => 'Edit, approve, delete, and requeue any rank action',
            self::OverrideRankActionRules     => 'Override the 30-day rank action rule',
            self::DemoteMembers               => 'Request demotions',
            self::AutoApproveJuniorPromotions => 'Auto-approve promotions up to Corporal',
            self::ManageMemberAwards          => 'Edit and delete member awards',
            self::ApproveAwards               => 'Approve member award requests',
            self::RequestAwards               => 'Request awards for members',
            self::ManageGlobalTags            => 'Manage clan-wide tags',
            self::ManageDivisionTags          => 'Create, edit, and delete division tags',
            self::AssignTags                  => 'Assign tags to members',
            self::UseSeniorLeaderTags         => 'See and use senior-leader-only tags',
            self::UseOfficerTags              => 'See and use officer-only tags',
            self::ManageAllTickets            => 'Work every ticket, including untyped ones',
            self::ManagePermissions           => 'Edit role permissions and grant abilities to users',
        };
    }

    public function resolvesAgainstStoredRole(): bool
    {
        return in_array($this, [
            self::Recruit,
            self::ConductTraining,
            self::UseAdvancedApiScopes,
        ], true);
    }
}
