<?php

namespace Tests\Unit\Rules;

use App\Rules\ResolvesToImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolvesToImageTest extends TestCase
{
    #[Test]
    public function it_passes_for_a_url_that_resolves_to_an_image()
    {
        Http::fake([
            'example.com/*' => Http::response('', 200, ['Content-Type' => 'image/png']),
        ]);

        $failed = false;
        (new ResolvesToImage)->validate('logo', 'https://example.com/logo.png', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    #[Test]
    public function it_fails_for_a_url_that_does_not_resolve_to_an_image()
    {
        Http::fake([
            'example.com/*' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $failed = false;
        (new ResolvesToImage)->validate('logo', 'https://example.com/page.html', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    #[Test]
    public function it_fails_for_a_url_that_returns_an_error_status()
    {
        Http::fake([
            'example.com/*' => Http::response('', 404),
        ]);

        $failed = false;
        (new ResolvesToImage)->validate('logo', 'https://example.com/missing.png', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    #[Test]
    public function it_fails_for_a_non_url_string_that_is_not_a_stored_image()
    {
        $failed = false;
        (new ResolvesToImage)->validate('logo', 'not-a-url', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    #[Test]
    public function it_passes_for_a_relative_path_that_exists_as_an_image_in_public_storage()
    {
        Storage::fake('public');
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        Storage::disk('public')->put('logos/existing.png', $pngBytes);

        $failed = false;
        (new ResolvesToImage)->validate('logo', 'logos/existing.png', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    #[Test]
    public function it_passes_for_an_empty_value()
    {
        $failed = false;
        (new ResolvesToImage)->validate('logo', '', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }
}
