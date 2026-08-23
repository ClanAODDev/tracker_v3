<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PromotionControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function confirm_rejects_unsigned_request()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->get(route('promotion.confirm', [$member->clan_id, $action]))
            ->assertForbidden();
    }

    #[Test]
    public function accept_rejects_unsigned_request()
    {
        Bus::fake();

        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->post(route('promotion.accept', [$member->clan_id, $action]))
            ->assertForbidden();

        $this->assertNull($action->fresh()->accepted_at);
        Bus::assertNotDispatched(UpdateRankForMember::class);
    }

    #[Test]
    public function decline_rejects_unsigned_request()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->post(route('promotion.decline', [$member->clan_id, $action]))
            ->assertForbidden();

        $this->assertNull($action->fresh()->declined_at);
    }

    #[Test]
    public function accept_succeeds_with_valid_signature()
    {
        Bus::fake();

        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $url = URL::temporarySignedRoute('promotion.accept', now()->addMinutes(10), [$member->clan_id, $action]);

        $this->post($url)->assertOk();

        $this->assertNotNull($action->fresh()->accepted_at);
        Bus::assertDispatched(UpdateRankForMember::class);
    }

    #[Test]
    public function decline_succeeds_with_valid_signature()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $url = URL::temporarySignedRoute('promotion.decline', now()->addMinutes(10), [$member->clan_id, $action]);

        $this->post($url)->assertOk();

        $this->assertNotNull($action->fresh()->declined_at);
    }

    #[Test]
    public function confirm_page_links_to_signed_accept_and_decline_urls()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $confirmUrl = URL::temporarySignedRoute('promotion.confirm', now()->addMinutes(10), [$member->clan_id, $action]);

        $response = $this->get($confirmUrl)->assertOk();

        $response->assertViewHas('acceptUrl');
        $response->assertViewHas('declineUrl');

        $acceptUrl = $response->viewData('acceptUrl');

        Bus::fake();
        $this->post($acceptUrl)->assertOk();
        $this->assertNotNull($action->fresh()->accepted_at);
    }

    #[Test]
    public function accept_signature_cannot_be_reused_for_decline()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $acceptUrl   = URL::temporarySignedRoute('promotion.accept', now()->addMinutes(10), [$member->clan_id, $action]);
        $queryString = parse_url($acceptUrl, PHP_URL_QUERY);
        $declineUrl  = route('promotion.decline', [$member->clan_id, $action]) . '?' . $queryString;

        $this->post($declineUrl)->assertForbidden();

        $this->assertNull($action->fresh()->declined_at);
    }
}
