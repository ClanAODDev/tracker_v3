<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Support\HeroImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OptimizeDivisionHeroImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function photoLikePng(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        mt_srand(1999);

        for ($y = 0; $y < $height; $y += 2) {
            for ($x = 0; $x < $width; $x += 2) {
                $grain = mt_rand(-12, 12);
                $color = imagecolorallocate(
                    $image,
                    max(0, min(255, intdiv($x * 255, $width) + $grain)),
                    max(0, min(255, intdiv($y * 255, $height) + $grain)),
                    max(0, min(255, 128 + $grain)),
                );
                imagefilledrectangle($image, $x, $y, $x + 1, $y + 1, $color);
            }
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    #[Test]
    public function it_replaces_an_oversized_header_image_and_keeps_the_position(): void
    {
        Storage::disk('public')->put('division-hero-images/huge.png', $this->photoLikePng(3200, 1200));
        $division = Division::factory(['hero_image' => 'division-hero-images/huge.png', 'hero_image_position' => '30% 70%'])->create();

        $this->artisan('tracker:optimize-division-hero-images')->assertSuccessful();

        $division->refresh();
        [$width, $height, $type] = getimagesizefromstring(Storage::disk('public')->get($division->hero_image));

        $this->assertStringStartsWith('division-hero-images/', $division->hero_image);
        $this->assertSame([HeroImageOptimizer::MAX_WIDTH, 960, IMAGETYPE_JPEG], [$width, $height, $type]);
        $this->assertSame('30% 70%', $division->hero_image_position);
        Storage::disk('public')->assertMissing('division-hero-images/huge.png');
    }

    #[Test]
    public function a_dry_run_changes_nothing(): void
    {
        Storage::disk('public')->put('division-hero-images/huge.png', $this->photoLikePng(3200, 1200));
        $division = Division::factory(['hero_image' => 'division-hero-images/huge.png'])->create();

        $this->artisan('tracker:optimize-division-hero-images', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('division-hero-images/huge.png', $division->fresh()->hero_image);
        Storage::disk('public')->assertExists('division-hero-images/huge.png');
    }

    #[Test]
    public function a_missing_file_is_skipped(): void
    {
        $division = Division::factory(['hero_image' => 'division-hero-images/gone.jpg'])->create();

        $this->artisan('tracker:optimize-division-hero-images')->assertSuccessful();

        $this->assertSame('division-hero-images/gone.jpg', $division->fresh()->hero_image);
    }
}
