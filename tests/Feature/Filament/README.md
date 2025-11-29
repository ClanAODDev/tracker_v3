# Filament Test Suite

This directory contains comprehensive tests for all Filament resources and functionality.

## Structure

```
tests/Feature/Filament/
├── README.md                       # This file
├── PanelAuthorizationTest.php     # Tests for panel access control
├── Admin/                          # Tests for Admin panel resources
│   ├── DivisionResourceTest.php
│   ├── MemberResourceTest.php
│   ├── AwardResourceTest.php
│   └── ...
└── Mod/                           # Tests for Mod panel resources
    ├── LeaveResourceTest.php
    ├── MemberResourceTest.php
    ├── PlatoonResourceTest.php
    └── ...
```

## Test Categories

### 1. Panel Authorization Tests
Location: `PanelAuthorizationTest.php`

Tests that verify:
- Admins can access admin, mod, and profile panels
- Officers can access mod and profile panels (but not admin)
- Sr_Ldr can access mod and profile panels (but not admin)
- Members can only access profile panel
- Developers can access all panels
- Unauthenticated users are redirected

### 2. Resource CRUD Tests
Location: `Admin/` and `Mod/` directories

Tests for each resource verify:
- Authorization (who can access the resource)
- List/index page renders correctly
- Create functionality with validation
- Update/edit functionality
- Delete functionality (soft delete where applicable)
- Restore functionality (for soft-deleted records)
- Table filters work correctly
- Custom form fields and behaviors

### 3. Form Validation Tests

Each resource test includes validation tests for:
- Required fields
- Field constraints (max length, date rules, etc.)
- Unique constraints
- Custom validation rules
- Conditional validation

### 4. Custom Behavior Tests

Tests for:
- Navigation badges (e.g., leave approval counts)
- Scoped queries (division-specific filtering)
- Readonly/disabled fields
- Custom actions and bulk actions
- Relation managers
- File uploads

## Running Tests

### Run all Filament tests
```bash
sail artisan test tests/Feature/Filament
```

### Run specific test file
```bash
sail artisan test tests/Feature/Filament/PanelAuthorizationTest.php
```

### Run specific test method
```bash
sail artisan test --filter test_admin_can_access_admin_panel
```

### Run tests for a specific panel
```bash
sail artisan test tests/Feature/Filament/Admin
sail artisan test tests/Feature/Filament/Mod
```

## Writing New Tests

### Base Test Case
All Filament tests should extend `Tests\FilamentTestCase` which provides:

- `createAdminUser()` - Creates an admin user
- `createOfficerUser()` - Creates an officer user
- `createModUser()` - Creates a moderator user
- `createMemberUser()` - Creates a regular member user
- `actingAsAdmin()` - Acts as admin user
- `actingAsOfficer()` - Acts as officer user
- `actingAsMod()` - Acts as moderator user
- `actingAsMember()` - Acts as member user
- `setAdminPanel()` - Sets current panel to admin
- `setModPanel()` - Sets current panel to mod
- `setProfilePanel()` - Sets current panel to profile
- `createDivisionWithStructure()` - Creates division with members
- `assertCanAccessResource()` - Asserts user can access resource
- `assertCannotAccessResource()` - Asserts user cannot access resource
- `assertCanRenderPage()` - Asserts page renders successfully

### Example Test Structure

```php
<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Admin\Resources\ExampleResource;
use App\Filament\Admin\Resources\ExampleResource\Pages\CreateExample;
use App\Filament\Admin\Resources\ExampleResource\Pages\EditExample;
use App\Filament\Admin\Resources\ExampleResource\Pages\ListExamples;
use App\Models\Example;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class ExampleResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdminPanel(); // or setModPanel()
    }

    public function test_admin_can_access_example_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(ExampleResource::class);
    }

    public function test_can_create_example(): void
    {
        $this->actingAsAdmin();

        $newData = [
            'name' => 'Test Example',
            'description' => 'Test Description',
        ];

        Livewire::test(CreateExample::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('examples', [
            'name' => 'Test Example',
        ]);
    }

    public function test_example_requires_name(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateExample::class)
            ->fillForm([
                'description' => 'Test Description',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }
}
```

## Test Coverage Goals

- [ ] Panel Authorization (✅ Complete)
- [ ] Admin Resources
  - [x] DivisionResource
  - [ ] MemberResource
  - [ ] AwardResource
  - [ ] LeaveResource
  - [ ] NoteResource
  - [ ] TicketResource
  - [ ] TicketTypeResource
  - [ ] TransferResource
  - [ ] UserResource
  - [ ] CensusResource
  - [ ] HandleResource
  - [ ] ActivityResource
  - [ ] RankActionResource
  - [ ] RoleResource
  - [ ] MemberRequestResource
- [ ] Mod Resources
  - [x] LeaveResource
  - [x] MemberResource
  - [ ] DivisionResource
  - [ ] PlatoonResource
  - [ ] SquadResource
  - [ ] MemberAwardResource
  - [ ] MemberRequestResource
  - [ ] TransferResource
  - [ ] RankActionResource
- [ ] Custom Actions
  - [ ] CleanupUnassignedLeadersAction
  - [ ] PartTimeMemberCleanupAction
  - [ ] CleanupTenureAwards
- [ ] Relation Managers
  - [ ] Awards
  - [ ] Notes
  - [ ] Transfers
  - [ ] RankActions
  - [ ] Platoons
  - [ ] Squads

## Best Practices

1. **One assertion per test method** when possible
2. **Descriptive test names** that explain what is being tested
3. **Use factories** for creating test data
4. **Clean up** after tests (RefreshDatabase trait handles this)
5. **Test both success and failure cases**
6. **Test authorization** before testing functionality
7. **Group related tests** in the same test class
8. **Use setup methods** to reduce duplication
9. **Test edge cases** and boundary conditions
10. **Keep tests isolated** - each test should be independent

## Common Assertions

### Form Assertions
- `assertHasNoFormErrors()` - No validation errors
- `assertHasFormErrors(['field' => 'rule'])` - Specific validation error
- `assertFormFieldExists('field')` - Field exists in form
- `assertFormFieldDoesNotExist('field')` - Field does not exist
- `assertFormFieldIsDisabled('field')` - Field is disabled
- `assertFormFieldIsHidden('field')` - Field is hidden

### Table Assertions
- `assertCanSeeTableRecords([$record])` - Records visible in table
- `assertCanNotSeeTableRecords([$record])` - Records not visible
- `filterTable('field', 'value')` - Apply table filter
- `sortTable('column')` - Sort table by column

### Database Assertions
- `assertDatabaseHas('table', ['field' => 'value'])` - Record exists
- `assertDatabaseMissing('table', ['field' => 'value'])` - Record doesn't exist
- `assertSoftDeleted('table', ['id' => 1])` - Record is soft deleted
