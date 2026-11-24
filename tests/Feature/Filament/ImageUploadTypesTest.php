<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Support\HeroImageOptimizer;
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

    #[Test]
    public function division_hero_image_accepts_png_files_and_saves_the_path(): void
    {
        $division = $this->actingAsCommandingOfficer();

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image', UploadedFile::fake()->image('hero.png', 1920, 820))
            ->call('save')
            ->assertHasNoFormErrors(['hero_image']);

        $path = $division->fresh()->hero_image;

        $this->assertStringStartsWith('division-hero-images/', $path);
        Storage::disk('public')->assertExists($path);
    }

    #[Test]
    public function division_hero_image_position_is_saved(): void
    {
        $division = $this->actingAsCommandingOfficer();
        $division->update(['hero_image' => 'division-hero-images/existing.jpg']);
        Storage::disk('public')->put('division-hero-images/existing.jpg', 'image');

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image_position', '30% 70%')
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('data.hero_image_position', '30% 70%');

        $this->assertSame('30% 70%', $division->fresh()->hero_image_position);
    }

    #[Test]
    public function removing_the_hero_image_clears_the_position(): void
    {
        $division = $this->actingAsCommandingOfficer();
        $division->update(['hero_image' => 'division-hero-images/existing.jpg', 'hero_image_position' => '30% 70%']);
        Storage::disk('public')->put('division-hero-images/existing.jpg', 'image');

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image', null)
            ->assertSet('data.hero_image_position', null);
    }

    #[Test]
    public function division_hero_image_position_rejects_values_outside_the_frame(): void
    {
        $division = $this->actingAsCommandingOfficer();
        $division->update(['hero_image' => 'division-hero-images/existing.jpg']);
        Storage::disk('public')->put('division-hero-images/existing.jpg', 'image');

        foreach (['150% 20%', 'center', '30% 70%; color: red'] as $position) {
            Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
                ->set('data.hero_image_position', $position)
                ->call('save')
                ->assertHasFormErrors(['hero_image_position']);
        }

        $this->assertNull($division->fresh()->hero_image_position);
    }

    #[Test]
    public function a_new_hero_image_resets_the_position(): void
    {
        $division = $this->actingAsCommandingOfficer();
        $division->update(['hero_image' => 'division-hero-images/existing.jpg', 'hero_image_position' => '30% 70%']);
        Storage::disk('public')->put('division-hero-images/existing.jpg', 'image');

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertSet('data.hero_image_position', '30% 70%')
            ->set('data.hero_image', [UploadedFile::fake()->image('new.png', 1920, 820)])
            ->assertSet('data.hero_image_position', null)
            ->set('data.hero_image_position', '20% 80%')
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('data.hero_image_position', '20% 80%');

        $division->refresh();

        $this->assertNotSame('division-hero-images/existing.jpg', $division->hero_image);
        $this->assertSame('20% 80%', $division->hero_image_position);
    }

    #[Test]
    public function large_hero_images_are_scaled_down_to_a_jpeg_without_cropping(): void
    {
        $division = $this->actingAsCommandingOfficer();

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image', UploadedFile::fake()->image('wide.png', 4000, 1600))
            ->call('save')
            ->assertHasNoFormErrors();

        $path                    = $division->fresh()->hero_image;
        [$width, $height, $type] = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertStringEndsWith('.jpg', $path);
        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertSame([HeroImageOptimizer::MAX_WIDTH, 1024], [$width, $height]);
    }

    #[Test]
    public function small_hero_images_are_not_upscaled(): void
    {
        $division = $this->actingAsCommandingOfficer();

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image', UploadedFile::fake()->image('small.png', 1600, 900))
            ->call('save')
            ->assertHasNoFormErrors();

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($division->fresh()->hero_image));

        $this->assertSame([1600, 900], [$width, $height]);
    }

    #[Test]
    public function division_hero_image_rejects_svg_files(): void
    {
        $division = $this->actingAsCommandingOfficer();

        $svg = UploadedFile::fake()->createWithContent(
            'hero.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.hero_image', $svg)
            ->call('save')
            ->assertHasFormErrors(['hero_image']);
    }
}
