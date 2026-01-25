<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class AwardImageRoutesTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    public function test_award_image_routes_return_png_images()
    {
        $member = $this->createMember();

        $this->get("/members/{$member->clan_id}/my-awards.png")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->get("/members/{$member->clan_id}/my-awards.png?award_count=4")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->get("/members/{$member->clan_id}/my-awards-cluster.png")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->get("/members/{$member->clan_id}-{$member->name}/my-awards.png")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function test_member_with_no_awards_returns_default_image()
    {
        $member = $this->createMember();

        $response = $this->get("/members/{$member->clan_id}/my-awards.png");

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $expectedImage = file_get_contents(public_path('images/dynamic-images/bgs/no-awards-base-image.png'));
        $this->assertEquals($expectedImage, $response->getContent());
    }

    public function test_award_image_route_returns_404_for_nonexistent_member()
    {
        $response = $this->get('/members/999999/my-awards.png');

        $response->assertNotFound();
    }
}
