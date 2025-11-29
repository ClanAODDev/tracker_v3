# AOD Tracker v3 - Authorization Policy Documentation

## Table of Contents
- [Executive Summary](#executive-summary)
- [Role Hierarchy](#role-hierarchy)
- [Position Hierarchy](#position-hierarchy)
- [Policy Summary Matrix](#policy-summary-matrix)
- [Detailed Policy Breakdown](#detailed-policy-breakdown)
- [Filament Authorization Patterns](#filament-authorization-patterns)
- [Common Authorization Patterns](#common-authorization-patterns)
- [Role-Based Access Matrix](#role-based-access-matrix)
- [Special Conditions & Business Rules](#special-conditions--business-rules)
- [Security Considerations](#security-considerations)
- [Usage Examples](#usage-examples)
- [Quick Reference Guide](#quick-reference-guide)

---

## Executive Summary

The AOD Tracker v3 implements a comprehensive role-based access control (RBAC) system through Laravel policies and Filament panel authorization. The system manages access to 14 different resources with varying levels of permissions based on user roles, positions, and relationships to the resources.

## Role Hierarchy

The system uses the following role hierarchy (from lowest to highest):

| Role ID | Role Name | Enum Value | Description |
|---------|-----------|------------|-------------|
| 1 | Member | `member` | Basic clan member |
| 2 | Officer | `officer` | Officers with limited management capabilities |
| 3 | Junior Leader | `jr_ldr` | Junior leadership role |
| 4 | Senior Leader | `sr_ldr` | Senior leadership with extensive permissions |
| 5 | Admin | `admin` | System administrators (MSgts, SGTs) |
| 6 | Banned | `banned` | Banned users with no access |
| - | Developer | - | Special role with full system access |

## Position Hierarchy

Leadership positions within divisions and platoons:

| Position | Value | Abbreviation | Description |
|----------|-------|--------------|-------------|
| Member | 1 | - | Regular member |
| Squad Leader | 2 | SL | Leads a squad |
| Platoon Leader | 3 | PL | Leads a platoon |
| Executive Officer | 5 | XO | Division second-in-command |
| Commanding Officer | 6 | CO | Division commander |
| Clan Admin | 7 | CA | Clan-level administrator |

## Policy Summary Matrix

| Policy | Resource | Primary Roles | Special Access | before() Bypass |
|--------|----------|---------------|----------------|-----------------|
| ApiTokenPolicy | API Tokens | Officer+ | Rank > Trainer | Admin, Developer |
| AwardPolicy | Awards | Developer Only | - | No |
| DivisionPolicy | Divisions | Admin, Sr Leader | Division Leaders | Developer |
| LeavePolicy | Leaves | Sr Leader, Admin | Non-members can create | No |
| MemberPolicy | Members | Admin, Sr Leader | Complex rank/position rules | Admin, Developer |
| MemberRequestPolicy | Recruitment | Admin, CO/XO | Request owners | Admin, Developer |
| NotePolicy | Notes | Officer+ | Division Leaders for edit/delete | Admin, Developer |
| PlatoonPolicy | Platoons | Sr Leader, Admin | Division/Platoon Leaders | Admin, Developer |
| RankActionPolicy | Rank Changes | Admin | Division/Platoon Leaders (view) | No |
| RankActionCommentsPolicy | Comments | All Users | Owner can delete | No |
| SquadPolicy | Squads | Sr Leader, Admin | Division/Platoon Leaders | Admin, Developer |
| TicketPolicy | Support Tickets | All Users | Ticket owner for view | Admin |
| TransferPolicy | Division Transfers | Division Leaders | Must be in active division | Admin, Developer |
| UserPolicy | Users | Developer, Admin Only | Specific actions for Sr Leader | Admin, Developer |

---

## Detailed Policy Breakdown

### 1. ApiTokenPolicy

**Resource:** Personal API tokens for authentication

**File:** `app/Policies/ApiTokenPolicy.php`

#### before() Method
Grants full access to:
- Developers (`isDeveloper()`)
- Admins (`isRole('admin')`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `create()` | Rank > Trainer AND role_id in [2,3,4,5] | Create new API token. Must be above Trainer rank and be Officer (2), Jr Leader (3), Sr Leader (4), or Admin (5) |
| `destroy()` | Token owner | Delete API token. Only if the token belongs to the authenticated user |

**Business Logic:**
- API token creation requires both sufficient rank and role
- Users can only delete their own tokens (not others')
- Role IDs: 2=Officer, 3=Jr Leader, 4=Sr Leader, 5=Admin

---

### 2. AwardPolicy

**Resource:** Member awards and commendations

**File:** `app/Policies/AwardPolicy.php`

#### before() Method
**None** - No global bypass

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `delete()` | Developer only | Delete individual award |
| `deleteAny()` | Developer only | Bulk delete awards |

**Business Logic:**
- Extremely restrictive - only developers can delete awards
- No before() method means even admins cannot delete awards
- Protects award integrity by preventing accidental deletion

---

### 3. DivisionPolicy

**Resource:** Game divisions (organizational units)

**File:** `app/Policies/DivisionPolicy.php`

#### before() Method
Grants full access to:
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `viewAny()` | Admin OR Division Leader in same division | List divisions. Division leaders can only see their own division |
| `view()` | Admin OR member of same division | View division details |
| `create()` | Admin only | Create new division |
| `update()` | Admin OR (Division Leader + Sr Leader role) | Update division settings. Division leaders must also have Sr Leader role |
| `delete()` | Always false | Divisions cannot be deleted |
| `show()` | Admin only | Show division in admin panel |

**Business Logic:**
- Division leaders (`isDivisionLeader()`) have limited access only to their own division
- Must be both a division leader AND have sr_ldr role to update
- Divisions are protected from deletion
- Regular members can view their own division but not others

---

### 4. LeavePolicy

**Resource:** Leave of absence records

**File:** `app/Policies/LeavePolicy.php`

#### before() Method
**None** - No global bypass

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `create()` | NOT member role | Create leave request. Any role except basic 'member' can create |
| `update()` | Admin OR Sr Leader | Update existing leave record |
| `deleteAny()` | Admin OR Sr Leader | Bulk delete leave records |

**Business Logic:**
- Officers and above can create leave records (for others)
- Only senior leadership can modify/delete leave records
- Basic members cannot create leave records (prevents self-service LoA)

---

### 5. MemberPolicy

**Resource:** Clan members (the core user entity)

**File:** `app/Policies/MemberPolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `viewAny()` | Always true | Anyone can list members |
| `view()` | Always true | Anyone can view member profiles |
| `recruit()` | NOT member role | Recruit new members. Officers+ only |
| `create()` | Always false | Direct creation disabled (use recruitment process) |
| `update()` | Sr Leader AND not self | Update member details. Cannot edit yourself |
| `delete()` | Always false | Members cannot be deleted (use separate instead) |
| `separate()` | Sr Leader AND not self AND target rank < your rank | Remove member from clan. Can only separate members of lower rank |
| `flagInactive()` | Officer OR Sr Leader | Mark member as inactive |
| `updateLeave()` | Sr Leader AND not self | Update member's leave status |
| `managePartTime()` | Self OR Officer+ | Manage part-time status. Can edit yourself or if Officer+ |
| `manageIngameHandles()` | Self OR Officer+ | Manage game handles. Can edit yourself or if Officer+ |
| `promote()` | Officer+ AND rank allowed AND same division | Promote member. Complex rules below |

**Promotion Business Logic:**
- Promoter must be Officer role or higher
- Can only promote up to one rank below your own rank (`promoter_rank - 1 >= target_rank`)
- Must be in same division as member being promoted
- Example: A Staff Sergeant (rank 6) can promote up to Corporal (rank 5)

**Complex Rules:**
- Self-service allowed only for part-time status and in-game handles
- Cannot edit, separate, or update leave status for yourself
- Rank-based restrictions prevent promoting equals or superiors
- Division-scoped permissions prevent cross-division promotions

---

### 6. MemberRequestPolicy

**Resource:** Recruitment requests (new member applications)

**File:** `app/Policies/MemberRequestPolicy.php`

#### before() Method
Grants full access to:
- Developers (`isDeveloper()`)
- Admins (`isRole('admin')`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `view()` | Always true | View recruitment requests |
| `create()` | Always true | Create recruitment request |
| `manage()` | Sr Leader + (CO or XO position) | Manage recruitment system. Must be Commanding Officer or Executive Officer |
| `update()` | Admin OR Sr Leader OR request owner | Update request. Can update if you created it |
| `cancel()` | Admin OR Sr Leader OR request owner | Cancel request. Can cancel if you created it |
| `delete()` | No implementation | Deletion not implemented |

**Business Logic:**
- Anyone can view and create recruitment requests (open recruitment)
- Only division CO/XO with Sr Leader role can manage the recruitment system
- Request creators can update/cancel their own requests
- Uses `requester_id` field to match against `user->member->clan_id`

---

### 7. NotePolicy

**Resource:** Member notes (administrative annotations)

**File:** `app/Policies/NotePolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `show()` | NOT member role | View notes. Officers and above only |
| `create()` | NOT member role | Create notes. Officers and above only |
| `edit()` | Division Leader | Edit existing note |
| `delete()` | Division Leader | Delete note |

**Business Logic:**
- Notes are hidden from basic members
- Officers can view and create notes but not edit/delete
- Only division leaders can edit/delete notes (data integrity)
- Admins/developers have full access via before() method

---

### 8. PlatoonPolicy

**Resource:** Platoons (sub-units within divisions)

**File:** `app/Policies/PlatoonPolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `create()` | Sr Leader | Create new platoon |
| `update()` | (Sr Leader OR Division Leader OR Platoon Leader) + same division | Update platoon details. Must be in same division |
| `delete()` | (Sr Leader OR Division Leader) + same division | Delete platoon. Must be in same division |

**Update Logic Details:**
- Sr Leaders can update any platoon in their division
- Division leaders can update platoons in their division
- Platoon leaders can update ONLY their own platoon (`member->clan_id == platoon->leader_id`)
- All updates require the platoon to be in the user's division

**Delete Logic Details:**
- Only Sr Leaders and Division Leaders can delete
- Must be in same division as the platoon

---

### 9. RankActionPolicy

**Resource:** Rank change requests/promotions

**File:** `app/Policies/RankActionPolicy.php`

#### before() Method
**None** - No global bypass

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `update()` | Complex visibility rules (see below) | View rank action request |
| `deleteAny()` | Admin only | Bulk delete rank actions |

**update() Visibility Rules (in priority order):**
1. **Cannot see own rank changes** - Returns false if record is for yourself
2. **Admins see all** - Admins can view any rank action
3. **Request creators** - Can view requests you created (`requester_id == member_id`)
4. **Division Leaders (CO/XO)** - Can view requests in their division for ranks ≤ Staff Sergeant
5. **Platoon Leaders** - Can view requests in their platoon for ranks below their own rank

**Business Logic:**
- Prevents users from viewing their own promotion requests (conflict of interest)
- Hierarchical visibility based on leadership position
- Division leaders have visibility cap at Staff Sergeant rank
- Platoon leaders limited to members below their rank in their platoon

---

### 10. RankActionCommentsPolicy

**Resource:** Comments on rank change requests (FilamentComment model)

**File:** `app/Policies/RankActionCommentsPolicy.php`

#### before() Method
**None** - No global bypass

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `viewAny()` | Always true | List all comments |
| `view()` | Always true | View individual comment |
| `create()` | Always true | Create comment |
| `update()` | Always false | Comments cannot be edited |
| `delete()` | Comment owner | Delete own comment only |
| `deleteAny()` | Always true | Bulk delete interface (respects individual delete rules) |
| `restore()` | Always false | No soft delete restoration |
| `restoreAny()` | Always false | No bulk restoration |
| `forceDelete()` | Always false | No force delete |
| `forceDeleteAny()` | Always false | No bulk force delete |

**Business Logic:**
- Open commenting system - anyone can view and create
- Comments are immutable - cannot be edited once created
- Users can only delete their own comments (`user->id === comment->user_id`)
- No soft delete or restore functionality

---

### 11. SquadPolicy

**Resource:** Squads (groups within platoons)

**File:** `app/Policies/SquadPolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `create()` | Sr Leader | Create new squad |
| `update()` | Sr Leader OR (Division Leader + same division) OR (Platoon Leader + owns platoon) | Update squad |
| `delete()` | Admin OR Sr Leader OR Division Leader OR (Platoon Leader + owns platoon) | Delete squad |
| `deleteAny()` | Admin OR Sr Leader OR Division Leader | Bulk delete squads |

**update() Rules:**
1. Sr Leaders can update any squad
2. Division leaders can update squads in their division
3. Platoon leaders can update squads in their platoon (`platoon->leader_id === user->member->clan_id`)

**delete() Rules:**
1. Admins and Sr Leaders can delete any squad
2. Division leaders can delete squads in their division
3. Platoon leaders can delete squads in their platoon

---

### 12. TicketPolicy

**Resource:** Support tickets / help requests

**File:** `app/Policies/TicketPolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `viewAny()` | Always true | List tickets |
| `view()` | Ticket creator | View ticket details. Only your own tickets |
| `create()` | Always true | Create support ticket |
| `update()` | Always true | Update ticket |
| `delete()` | Always true | Delete ticket |
| `restore()` | Always true | Restore soft-deleted ticket |
| `forceDelete()` | Always true | Permanently delete ticket |
| `manage()` | Always false | Manage ticket system (reserved for admins via before()) |
| `createComment()` | Always true | Add comment to ticket |
| `deleteComment()` | Always true | Delete ticket comment |

**Business Logic:**
- Open ticket system for all users
- Users can only view their own tickets (`user->id === ticket->caller_id`)
- Admins (via before()) can manage all tickets
- Full CRUD operations available to ticket owners

---

### 13. TransferPolicy

**Resource:** Division transfer requests

**File:** `app/Policies/TransferPolicy.php`

#### before() Method
Grants full access to:
- Admins (`isRole('admin')`)
- Developers (`isDeveloper()`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `create()` | User's division is active | Create transfer request. Source division must be active |
| `approve()` | Division Leader of target division | Approve transfer. Must be leader of the division being transferred TO |

**Business Logic:**
- Members can only create transfers if their current division is active (`user->division->isActive()`)
- Approval requires being the leader of the **target** division, not the source
- Prevents transfers out of inactive divisions
- Ensures target division leadership approves incoming transfers

---

### 14. UserPolicy

**Resource:** User accounts and system access

**File:** `app/Policies/UserPolicy.php`

#### before() Method
Grants full access to:
- Developers (`isDeveloper()`)
- Admins (`isRole('admin')`)

#### Methods

| Method | Required Role/Condition | Description |
|--------|-------------------------|-------------|
| `viewAny()` | Always false | List users (admin only via before()) |
| `view()` | Always false | View user details (admin only via before()) |
| `create()` | Always false | Create user (admin only via before()) |
| `update()` | Always false | Update user (admin only via before()) |
| `delete()` | Always false | Delete user (admin only via before()) |
| `restore()` | Always false | Restore user (admin only via before()) |
| `forceDelete()` | Always false | Force delete user (admin only via before()) |
| `canImpersonate()` | Always false | Impersonate other users (disabled) |
| `viewDivisionStructure()` | Officer OR Sr Leader | View division organizational chart |
| `editDivisionStructure()` | Sr Leader | Edit division organizational structure |
| `manageUnassigned()` | Sr Leader | Manage unassigned members |
| `train()` | Rank > Sergeant AND role_id in [4,5] | Access training features. Must be Sr Leader (4) or Admin (5) with rank above Sergeant |

**Business Logic:**
- Standard CRUD operations locked to admins/developers via before()
- Impersonation completely disabled
- Division structure viewing requires Officer+ role
- Training features require both high rank (>Sergeant) and senior role (Sr Leader or Admin)
- Role IDs: 4=Sr Leader, 5=Admin

---

## Filament Authorization Patterns

### Panel-Level Access Control

The application has three Filament panels with distinct access levels:

#### Admin Panel (`/admin`)
- **Path:** `/admin`
- **Access Control:**
  - Only `Role::ADMIN` can access
  - Developers can bypass this restriction
- **Resources:** Full administrative resources for all models

#### Mod Panel (`/operations`)
- **Path:** `/operations`
- **Access Control:**
  - Roles allowed: `ADMIN`, `SENIOR_LEADER`, `OFFICER`
  - Controlled via `Role::canAccessPanel('mod')`
- **Resources:** Operations-focused resources with division-scoped access

#### Profile Panel (`/profile`)
- **Path:** `/profile`
- **Access Control:**
  - Roles allowed: `ADMIN`, `SENIOR_LEADER`, `OFFICER`, `MEMBER`
  - Most permissive panel for member self-service

### Implementation

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    if ($this->isDeveloper()) {
        return true;
    }

    if (!$this->role instanceof RoleEnum) {
        return false;
    }

    return $this->role->canAccessPanel($panel->getId());
}

// app/Enums/Role.php
public function canAccessPanel(string $panelId): bool
{
    if ($this === self::BANNED) {
        return false;
    }

    return match ($panelId) {
        'admin' => $this === self::ADMIN,
        'mod' => in_array($this, [self::ADMIN, self::SENIOR_LEADER, self::OFFICER]),
        'profile' => in_array($this, [self::ADMIN, self::SENIOR_LEADER, self::OFFICER, self::MEMBER]),
        default => false,
    };
}
```

---

### Query Scoping

Resources use `modifyQueryUsing()` and `getEloquentQuery()` to restrict data access based on user context.

#### Division Scoping

Most Mod panel resources scope data to the authenticated user's division:

```php
// app/Filament/Mod/Resources/LeaveResource.php
->modifyQueryUsing(function ($query) {
    $query->whereHas('member', function ($memberQuery) {
        $memberQuery->where('division_id', auth()->user()->member->division_id);
    });
})
```

```php
// app/Filament/Mod/Resources/PlatoonResource.php
->modifyQueryUsing(function ($query) {
    $query->where('division_id', auth()->user()->member->division_id);
})
```

```php
// app/Filament/Mod/Resources/SquadResource.php
->modifyQueryUsing(function ($query) {
    $query->whereHas('platoon', function ($query) {
        $query->where('division_id', auth()->user()->member->division_id);
    });
})
```

---

### Navigation Badges

Navigation badges provide visual indicators and are scoped to user permissions.

#### Role-Based Badges

```php
// app/Filament/Mod/Resources/LeaveResource.php
public static function getNavigationBadge(): ?string
{
    if (auth()->user()->isRole(['admin', 'sr_ldr'])) {
        $divisionId = auth()->user()->member->division_id;

        return (string) static::$model::where('approver_id', null)
            ->whereHas('member', function ($memberQuery) use ($divisionId) {
                $memberQuery->where('division_id', $divisionId);
            })->count();
    }

    return null;
}
```

---

### Action Authorization

Actions within resources use `visible()` and `hidden()` callbacks for fine-grained authorization.

#### Table Actions

```php
// app/Filament/Mod/Resources/MemberResource.php
BulkAction::make('member_transfer')
    ->visible(fn(): bool => auth()->user()->isRole(['admin', 'sr_ldr']))
    ->action(function (Collection $records, array $data): void {
        // ...
    })
```

#### Complex Multi-Condition Actions

```php
// app/Filament/Mod/Resources/RankActionResource/Pages/EditRankAction.php
Actions\DeleteAction::make('delete')
    ->label('Cancel Action')
    ->hidden(fn (RankAction $action) => $action->member->division_id === 0)
    ->visible(fn (RankAction $action) =>
        auth()->user()->isDivisionLeader()
        || auth()->user()->isRole('admin')
        || auth()->user()->member_id == $action->requester_id
    )
    ->requiresConfirmation()
```

#### Transfer Actions

```php
// app/Filament/Mod/Resources/TransferResource.php
Action::make('Approve')
    ->visible(fn (Transfer $record) => !$record->approved_at && !$record->hold_placed_at)
    ->action(function (Transfer $record) {
        $record->approve();
        UpdateDivisionForMember::dispatch($record);
    })

Tables\Actions\BulkActionGroup::make([...])
    ->visible(fn (Transfer $record) => $record->canApprove())
    ->label('Manage')
```

---

### Custom Authorization Methods

Several custom methods on the `User` model provide reusable authorization logic.

#### Position-Based Methods

```php
// app/Models/User.php
public function isDivisionLeader(): bool
{
    if (!$member = $this->member) {
        return false;
    }
    return in_array($member->position, [
        Position::COMMANDING_OFFICER,
        Position::EXECUTIVE_OFFICER,
    ]);
}
```

#### Context-Specific Authorization

```php
// app/Models/User.php
public function canManageTransferCommentsFor(Transfer $transfer): bool
{
    return $this->isAdminOrDivisionLeader();
}

public function canManageRankActionCommentsFor(RankAction $action): bool
{
    $userRank = $this->member->rank;
    $newRank = $action->rank;

    // For Sergeant and above, require Master Sergeant
    if ($newRank->value >= Rank::SERGEANT->value) {
        return $userRank->value >= Rank::MASTER_SERGEANT->value;
    }

    // Platoon leaders within their authorized range
    if ($this->isWithinPlatoonLimit($newRank, $this->division)) {
        return true;
    }

    // Division Leaders or Admins
    return $this->isAdminOrDivisionLeader();
}
```

---

## Common Authorization Patterns

### 1. Self-Prevention Pattern
Several policies prevent users from performing actions on themselves:
- **MemberPolicy:** Cannot update, separate, or update leave for yourself
- **RankActionPolicy:** Cannot view your own rank change requests

**Rationale:** Prevents conflicts of interest and self-service in sensitive operations

### 2. Hierarchical Access Pattern
Policies enforce organizational hierarchy:
- **MemberPolicy.separate():** Can only separate members of lower rank
- **MemberPolicy.promote():** Can only promote to one rank below your own
- **RankActionPolicy:** Platoon leaders see only lower ranks in their platoon

**Rationale:** Maintains command structure integrity

### 3. Division-Scoped Pattern
Many policies restrict actions to same division:
- **DivisionPolicy:** Division leaders can only manage their own division
- **PlatoonPolicy:** All operations require same division
- **MemberPolicy.promote():** Must be in same division

**Rationale:** Prevents cross-division interference

### 4. Developer Bypass Pattern
Most policies grant full access to developers:
- Present in: ApiToken, Division, Member, MemberRequest, Note, Platoon, Squad, Transfer, User
- **NOT** present in: Award (extra protection), Leave, RankAction, RankActionComments, Ticket

**Rationale:** Developers need system-wide access for maintenance, but critical resources protected

### 5. Position-Based Pattern
Leadership positions grant additional permissions:
- **Division Leaders (CO/XO):** Enhanced access to division resources
- **Platoon Leaders:** Can manage their platoon's resources
- **Squad Leaders:** Limited management within squad

**Rationale:** Operational leadership requires management capabilities

---

## Role-Based Access Matrix

| Action Category | Member | Officer | Jr Leader | Sr Leader | Admin | Developer |
|----------------|--------|---------|-----------|-----------|-------|-----------|
| View Members | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Create Notes | ✗ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Edit/Delete Notes | ✗ | ✗ | ✗ | Division Leaders | ✓ | ✓ |
| Update Members | ✗ | ✗ | ✗ | ✓ | ✓ | ✓ |
| Separate Members | ✗ | ✗ | ✗ | ✓* | ✓ | ✓ |
| Promote Members | ✗ | ✓* | ✓* | ✓* | ✓ | ✓ |
| Create Leaves | ✗ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Update/Delete Leaves | ✗ | ✗ | ✗ | ✓ | ✓ | ✓ |
| Create Divisions | ✗ | ✗ | ✗ | ✗ | ✓ | ✓ |
| Update Divisions | ✗ | ✗ | ✗ | Division Leaders* | ✓ | ✓ |
| Create Platoons | ✗ | ✗ | ✗ | ✓ | ✓ | ✓ |
| Create Squads | ✗ | ✗ | ✗ | ✓ | ✓ | ✓ |
| Manage Recruitment | ✗ | ✗ | ✗ | CO/XO only | ✓ | ✓ |
| Delete Awards | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ |
| Create API Tokens | ✗ | ✓* | ✓* | ✓* | ✓ | ✓ |
| Flag Inactive | ✗ | ✓ | ✗ | ✓ | ✓ | ✓ |
| View Division Structure | ✗ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Edit Division Structure | ✗ | ✗ | ✗ | ✓ | ✓ | ✓ |
| Access Training | ✗ | ✗ | ✗ | ✓* | ✓* | ✓ |

*Conditional access based on additional requirements (rank, position, division, etc.)

---

## Special Conditions & Business Rules

### Rank-Based Restrictions

1. **API Token Creation**
   - Requires rank > Trainer
   - Prevents junior members from API access

2. **Member Separation**
   - Can only separate members of lower rank
   - Prevents subordinates from separating superiors

3. **Member Promotion**
   - Can only promote to one rank below your own
   - Must be in same division
   - Prevents rank inflation

4. **Training Access**
   - Must be rank > Sergeant
   - Must have Sr Leader or Admin role
   - Ensures qualified trainers

### Position-Based Restrictions

1. **Recruitment Management**
   - Must be CO (Commanding Officer) or XO (Executive Officer)
   - Must have Sr Leader role
   - Ensures division leadership controls recruitment

2. **Division Leadership**
   - CO/XO get enhanced division access
   - Can update division settings if also Sr Leader
   - Limited to their own division

3. **Platoon Leadership**
   - Platoon leaders can manage their platoon
   - Cannot manage other platoons
   - Must be identified by `clan_id == platoon->leader_id`

### Division-Scoped Restrictions

1. **Cross-Division Prevention**
   - Cannot promote members in other divisions
   - Cannot manage platoons/squads in other divisions
   - Division leaders limited to their division

2. **Transfer Approval**
   - Must be leader of TARGET division (not source)
   - Ensures incoming transfers are vetted
   - Source division must be active

### Self-Service Restrictions

1. **Allowed Self-Service**
   - Manage own part-time status
   - Manage own in-game handles
   - Create support tickets

2. **Prevented Self-Service**
   - Cannot update own member record
   - Cannot view own rank change requests
   - Cannot separate yourself
   - Cannot update own leave status

---

## Security Considerations

### 1. Immutable Records
- **Awards:** Only developers can delete (extreme protection)
- **Divisions:** Cannot be deleted at all
- **Members:** Cannot be deleted (use separate instead)
- **Comments:** Cannot be edited once created

### 2. Conflict of Interest Prevention
- Cannot view own rank change requests
- Cannot update own member record (except specific fields)
- Cannot separate yourself

### 3. Hierarchical Integrity
- Rank-based separation prevents subordinates removing superiors
- Promotion limits prevent rank inflation
- Division-scoped operations prevent cross-division interference

### 4. Audit Trail Protection
- Comments cannot be edited (preserves discussion history)
- Awards cannot be deleted except by developers
- Notes restricted to division leaders for edit/delete

### 5. Developer Safeguards
- Most policies have developer bypass
- Critical resources (Awards) protected even from admins
- System-wide access for maintenance without compromising data

---

## Usage Examples

### Example 1: Promoting a Member
```php
// User (Staff Sergeant, Officer role) attempts to promote Corporal in same division
$canPromote = Gate::allows('promote', [$user, $member]);

// Checks performed:
// 1. User has Officer role or higher ✓
// 2. User rank (6) - 1 >= Member rank (5) ✓
// 3. Same division ✓
// Result: Allowed
```

### Example 2: Viewing Division
```php
// Division Leader attempts to view another division
$canView = Gate::allows('view', [$user, $division]);

// Checks performed:
// 1. Not admin (bypass)
// 2. User division_id (1) === Division id (2) ✗
// Result: Denied
```

### Example 3: Creating Leave
```php
// Basic member attempts to create leave
$canCreate = Gate::allows('create', Leave::class);

// Checks performed:
// 1. User role !== 'member' ✗
// Result: Denied (must be Officer+)
```

### Example 4: Managing Recruitment
```php
// Sr Leader with XO position attempts to manage recruitment
$canManage = Gate::allows('manage', MemberRequest::class);

// Checks performed:
// 1. Not admin/developer (no bypass)
// 2. User role === 'sr_ldr' ✓
// 3. User position in [CO, XO] ✓
// Result: Allowed
```

### Example 5: Deleting Award
```php
// Admin attempts to delete award
$canDelete = Gate::allows('delete', $award);

// Checks performed:
// 1. No before() bypass for admins
// 2. User isDeveloper() ✗
// Result: Denied (only developers can delete awards)
```

---

## Quick Reference Guide

### Most Restrictive Policies
1. **AwardPolicy** - Only developers can delete
2. **UserPolicy** - Admin/developer only for most operations
3. **DivisionPolicy** - No deletion allowed

### Most Permissive Policies
1. **TicketPolicy** - Open CRUD for ticket owners
2. **MemberRequestPolicy** - Anyone can view/create
3. **RankActionCommentsPolicy** - Open viewing/commenting

### Policies with Complex Logic
1. **MemberPolicy** - 11 methods with varying rank/position rules
2. **RankActionPolicy** - Hierarchical visibility based on position
3. **DivisionPolicy** - Division-scoped with leadership rules

### Policies Without before() Bypass
1. **AwardPolicy** - Extra protection even from admins
2. **LeavePolicy** - Standard role-based access
3. **RankActionPolicy** - No global bypass
4. **RankActionCommentsPolicy** - Owner-only deletion

---

**Document Version:** 1.0
**Last Updated:** 2025-11-27
**Policies Analyzed:** 14
**Total Policy Methods:** 73
**Filament Panels:** 3 (Admin, Mod, Profile)
