<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ImageUploadTypesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
        Storage::fake('public');
    }

    private function actingAsCommandingOfficer()
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);

        $this->actingAs($co);

        return $division;
    }

    #[Test]
    public function division_screenshots_reject_svg_files(): void
    {
        $division = $this->actingAsCommandingOfficer();

        $svg = UploadedFile::fake()->createWithContent(
            'shot.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.screenshots', [$svg])
            ->call('save')
            ->assertHasFormErrors(['screenshots']);
    }

    #[Test]
    public function division_screenshots_accept_png_files(): void
    {
        $division = $this->actingAsCommandingOfficer();

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.screenshots', [UploadedFile::fake()->image('shot.png')])
            ->call('save')
            ->assertHasNoFormErrors(['screenshots']);
    }
}
