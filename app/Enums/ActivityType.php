<?php

namespace App\Enums;

enum ActivityType: int
{
    case RECRUITED = 1;
    case CREATED_MEMBER = 2;
    case REMOVED = 3;
    case FLAGGED = 4;
    case UNFLAGGED = 5;
    case TRANSFERRED = 6;
    case ASSIGNED_PLATOON = 7;
    case ASSIGNED_SQUAD = 8;
    case UNASSIGNED = 9;
    case REQUESTED_LEAVE = 10;
    case APPROVED_LEAVE = 11;
    case EXTENDED_LEAVE = 12;
    case ENDED_LEAVE = 13;
    case ADD_PART_TIME = 14;
    case REMOVE_PART_TIME = 15;
    case CREATED_NOTE = 16;
    case UPDATED_NOTE = 17;
    case DELETED_NOTE = 18;
    case CREATED_PLATOON = 19;
    case UPDATED_PLATOON = 20;
    case DELETED_PLATOON = 21;
    case CREATED_SQUAD = 22;
    case UPDATED_SQUAD = 23;
    case DELETED_SQUAD = 24;
    case UPDATED_STRUCTURE = 25;
    case ROLE_GRANTED = 26;
    case CREATED_DIVISION = 27;
    case DELETED_DIVISION = 28;

    public function label(): string
    {
        return match ($this) {
            self::RECRUITED => 'Recruited',
            self::CREATED_MEMBER => 'Created member',
            self::REMOVED => 'Removed',
            self::FLAGGED => 'Flagged',
            self::UNFLAGGED => 'Unflagged',
            self::TRANSFERRED => 'Transferred',
            self::ASSIGNED_PLATOON => 'Assigned platoon',
            self::ASSIGNED_SQUAD => 'Assigned squad',
            self::UNASSIGNED => 'Unassigned',
            self::REQUESTED_LEAVE => 'Requested leave',
            self::APPROVED_LEAVE => 'Approved leave',
            self::EXTENDED_LEAVE => 'Extended leave',
            self::ENDED_LEAVE => 'Ended leave',
            self::ADD_PART_TIME => 'Added part-time',
            self::REMOVE_PART_TIME => 'Removed part-time',
            self::CREATED_NOTE => 'Created note',
            self::UPDATED_NOTE => 'Updated note',
            self::DELETED_NOTE => 'Deleted note',
            self::CREATED_PLATOON => 'Created platoon',
            self::UPDATED_PLATOON => 'Updated platoon',
            self::DELETED_PLATOON => 'Deleted platoon',
            self::CREATED_SQUAD => 'Created squad',
            self::UPDATED_SQUAD => 'Updated squad',
            self::DELETED_SQUAD => 'Deleted squad',
            self::UPDATED_STRUCTURE => 'Updated structure',
            self::ROLE_GRANTED => 'Role granted',
            self::CREATED_DIVISION => 'Created division',
            self::DELETED_DIVISION => 'Deleted division',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function feedTypes(): array
    {
        return [
            self::RECRUITED,
            self::REMOVED,
            self::TRANSFERRED,
            self::FLAGGED,
            self::UNFLAGGED,
            self::REQUESTED_LEAVE,
            self::APPROVED_LEAVE,
            self::EXTENDED_LEAVE,
            self::ENDED_LEAVE,
            self::ADD_PART_TIME,
            self::REMOVE_PART_TIME,
            self::ROLE_GRANTED,
        ];
    }

    public function feedIcon(): string
    {
        return match ($this) {
            self::RECRUITED => 'fa fa-user-plus text-success',
            self::REMOVED => 'fa fa-user-minus text-danger',
            self::TRANSFERRED => 'fa fa-exchange-alt text-info',
            self::FLAGGED => 'fa fa-flag text-warning',
            self::UNFLAGGED => 'fa fa-flag-checkered text-muted',
            self::REQUESTED_LEAVE, self::APPROVED_LEAVE, self::EXTENDED_LEAVE => 'fa fa-clock text-warning',
            self::ENDED_LEAVE => 'fa fa-check-circle text-success',
            self::ADD_PART_TIME => 'fa fa-plus-circle text-info',
            self::REMOVE_PART_TIME => 'fa fa-minus-circle text-muted',
            self::ROLE_GRANTED => 'fa fa-shield-alt text-accent',
            default => 'fa fa-circle text-muted',
        };
    }

    public function feedDescription(int $count = 1): string
    {
        $wasWere = $count === 1 ? 'was' : 'were';

        return match ($this) {
            self::RECRUITED => "{$wasWere} recruited",
            self::REMOVED => "{$wasWere} removed",
            self::TRANSFERRED => 'transferred',
            self::FLAGGED => "{$wasWere} flagged for inactivity",
            self::UNFLAGGED => "{$wasWere} unflagged",
            self::REQUESTED_LEAVE => 'requested leave',
            self::APPROVED_LEAVE => "{$wasWere} approved for leave",
            self::EXTENDED_LEAVE => 'had leave extended',
            self::ENDED_LEAVE => 'returned from leave',
            self::ADD_PART_TIME => "{$wasWere} added as part-time",
            self::REMOVE_PART_TIME => "{$wasWere} removed from part-time",
            self::ROLE_GRANTED => "{$wasWere} granted " . ($count === 1 ? 'a role' : 'roles'),
            default => $this->label(),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::RECRUITED => 'success',
            self::REMOVED => 'danger',
            self::FLAGGED => 'warning',
            self::UNFLAGGED => 'info',
            self::TRANSFERRED => 'primary',
            default => 'gray',
        };
    }

    public static function fromLegacyName(string $name): ?self
    {
        return match ($name) {
            'recruited_member', 'recruited' => self::RECRUITED,
            'created_member' => self::CREATED_MEMBER,
            'removed_member', 'removed' => self::REMOVED,
            'flagged_member', 'flagged' => self::FLAGGED,
            'unflagged_member', 'unflagged' => self::UNFLAGGED,
            'transferred_member', 'transferred' => self::TRANSFERRED,
            'assigned_platoon_member', 'assigned_platoon' => self::ASSIGNED_PLATOON,
            'assigned_squad_member', 'assigned_squad' => self::ASSIGNED_SQUAD,
            'unassigned_member', 'unassigned' => self::UNASSIGNED,
            'granted_leave_member', 'granted_leave', 'requested_leave' => self::REQUESTED_LEAVE,
            'approved_leave_member', 'approved_leave' => self::APPROVED_LEAVE,
            'extended_leave_member', 'extended_leave' => self::EXTENDED_LEAVE,
            'ended_leave_member', 'ended_leave' => self::ENDED_LEAVE,
            'add_part_time_member', 'add_part_time' => self::ADD_PART_TIME,
            'remove_part_time_member', 'remove_part_time' => self::REMOVE_PART_TIME,
            'created_note' => self::CREATED_NOTE,
            'updated_note' => self::UPDATED_NOTE,
            'deleted_note' => self::DELETED_NOTE,
            'created_platoon' => self::CREATED_PLATOON,
            'updated_platoon' => self::UPDATED_PLATOON,
            'deleted_platoon' => self::DELETED_PLATOON,
            'created_squad' => self::CREATED_SQUAD,
            'updated_squad' => self::UPDATED_SQUAD,
            'deleted_squad' => self::DELETED_SQUAD,
            'updated_structure_division', 'updated_structure' => self::UPDATED_STRUCTURE,
            'role_granted_to_member' => self::ROLE_GRANTED,
            'created_division' => self::CREATED_DIVISION,
            'deleted_division' => self::DELETED_DIVISION,
            default => null,
        };
    }
}
