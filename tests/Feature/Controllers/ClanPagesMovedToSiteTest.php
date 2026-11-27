<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class ClanPagesMovedToSiteTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    public static function movedPages(): array
    {
        return [
            'ranking structure' => ['/clan/ranks', '/ranks'],
            'code of conduct'   => ['/clan/code-of-conduct', '/code-of-conduct'],
        ];
    }

    #[Test]
    #[DataProvider('movedPages')]
    public function old_tracker_links_redirect_permanently_to_the_website(string $old, string $new): void
    {
        config(['services.aod_site.url' => 'https://www.example.test']);

        $this->actingAs($this->createMemberWithUser())
            ->get($old)
            ->assertStatus(301)
            ->assertRedirect("https://www.example.test{$new}");
    }

    #[Test]
    public function the_menu_links_to_the_website_pages(): void
    {
        config(['services.aod_site.url' => 'https://www.example.test']);

        $this->actingAs($this->createMemberWithUser())
            ->get(route('home'))
            ->assertInertia(fn ($page) => $page->where('nav', function ($nav) {
                $json = json_encode($nav);
                $this->assertStringContainsString('https:\/\/www.example.test\/ranks', $json);
                $this->assertStringContainsString('https:\/\/www.example.test\/code-of-conduct', $json);

                return true;
            }));
    }
}
