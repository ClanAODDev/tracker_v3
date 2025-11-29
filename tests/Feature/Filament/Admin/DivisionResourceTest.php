<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Admin\Resources\DivisionResource;
use App\Filament\Admin\Resources\DivisionResource\Pages\CreateDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\ListDivisions;
use App\Models\Division;
use App\Models\Handle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class DivisionResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdminPanel();
        Storage::fake('public');
    }

    public function test_admin_can_access_division_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(DivisionResource::class);
    }

    public function test_officer_cannot_access_division_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCannotAccessResource(DivisionResource::class);
    }

    public function test_can_list_divisions(): void
    {
        $this->actingAsAdmin();

        $division = Division::factory()->create();

        Livewire::test(ListDivisions::class)
            ->assertCanSeeTableRecords([$division]);
    }

    public function test_can_create_division(): void
    {
        $this->actingAsAdmin();

        $handle = Handle::factory()->create();

        $newData = [
            'name' => 'Test Division',
            'abbreviation' => 'TST',
            'description' => 'Test Division Description',
            'handle_id' => $handle->id,
            'logo' => UploadedFile::fake()->image('logo.png'),
            'officer_role_id' => 123,
            'forum_app_id' => 456,
        ];

        Livewire::test(CreateDivision::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('divisions', [
            'name' => 'Test Division',
            'abbreviation' => 'TST',
        ]);
    }

    public function test_division_requires_name(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDivision::class)
            ->fillForm([
                'abbreviation' => 'TST',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_division_requires_abbreviation(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDivision::class)
            ->fillForm([
                'name' => 'Test Division',
            ])
            ->call('create')
            ->assertHasFormErrors(['abbreviation' => 'required']);
    }

    public function test_division_requires_logo(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDivision::class)
            ->fillForm([
                'name' => 'Test Division',
                'abbreviation' => 'TST',
            ])
            ->call('create')
            ->assertHasFormErrors(['logo' => 'required']);
    }

    public function test_division_abbreviation_max_length_is_three(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDivision::class)
            ->fillForm([
                'name' => 'Test Division',
                'abbreviation' => 'TOOLONG',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->call('create')
            ->assertHasFormErrors(['abbreviation']);
    }

    public function test_can_edit_division(): void
    {
        $this->actingAsAdmin();

        $division = Division::factory()->create([
            'name' => 'Original Name',
        ]);

        Livewire::test(EditDivision::class, ['record' => $division->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_soft_delete_division(): void
    {
        $this->actingAsAdmin();

        $division = Division::factory()->create();

        Livewire::test(EditDivision::class, ['record' => $division->id])
            ->callAction('delete');

        $this->assertSoftDeleted('divisions', [
            'id' => $division->id,
        ]);
    }

    public function test_can_restore_soft_deleted_division(): void
    {
        $this->actingAsAdmin();

        $division = Division::factory()->create();
        $division->delete();

        Livewire::test(EditDivision::class, ['record' => $division->id])
            ->callAction('restore');

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_filter_divisions_by_active_status(): void
    {
        $this->actingAsAdmin();

        $activeDivision = Division::factory()->create(['active' => true]);
        $inactiveDivision = Division::factory()->inactive()->create();

        Livewire::test(ListDivisions::class)
            ->filterTable('is_active')
            ->assertCanSeeTableRecords([$activeDivision])
            ->assertCanNotSeeTableRecords([$inactiveDivision]);
    }

    public function test_can_filter_divisions_by_trashed(): void
    {
        $this->actingAsAdmin();

        $activeDivision = Division::factory()->create();
        $trashedDivision = Division::factory()->create();
        $trashedDivision->delete();

        Livewire::test(ListDivisions::class)
            ->assertCanSeeTableRecords([$activeDivision])
            ->assertCanNotSeeTableRecords([$trashedDivision]);
    }

    public function test_settings_field_hidden_on_create(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDivision::class)
            ->assertFormFieldDoesNotExist('settings');
    }

    public function test_settings_field_visible_on_edit(): void
    {
        $this->actingAsAdmin();

        $division = Division::factory()->create();

        Livewire::test(EditDivision::class, ['record' => $division->id])
            ->assertFormFieldExists('settings');
    }

    public function test_logo_is_uploaded_to_logos_directory(): void
    {
        $this->actingAsAdmin();

        $handle = Handle::factory()->create();
        $logo = UploadedFile::fake()->image('logo.png');

        Livewire::test(CreateDivision::class)
            ->fillForm([
                'name' => 'Test Division',
                'abbreviation' => 'TST',
                'handle_id' => $handle->id,
                'logo' => $logo,
                'officer_role_id' => 123,
                'forum_app_id' => 456,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $division = Division::where('name', 'Test Division')->first();

        Storage::disk('public')->assertExists('logos/' . basename($division->logo));
    }
}
