<?php

namespace Tests\Unit\Services;

use App\Data\DivisionShowData;
use App\Models\Census;
use App\Services\DivisionShowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionShowServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private DivisionShowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DivisionShowService::class);
    }

    #[Test]
    public function get_show_data_returns_division_show_data_object()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertInstanceOf(DivisionShowData::class, $result);
    }

    #[Test]
    public function get_show_data_includes_division()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertTrue($result->division->is($division));
    }

    #[Test]
    public function get_show_data_includes_stats()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertNotNull($result->stats);
    }

    #[Test]
    public function get_show_data_includes_chart_data()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertNotNull($result->chartData);
    }

    #[Test]
    public function get_show_data_includes_platoons()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertCount(1, $result->platoons);
    }

    #[Test]
    public function get_show_data_platoons_ordered_by_order_field()
    {
        $division = $this->createActiveDivision();
        $platoon2 = $this->createPlatoon($division, ['order' => 2, 'name' => 'Second Platoon']);
        $platoon1 = $this->createPlatoon($division, ['order' => 1, 'name' => 'First Platoon']);
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertEquals('First Platoon', $result->platoons->first()->name);
        $this->assertEquals('Second Platoon', $result->platoons->last()->name);
    }

    #[Test]
    public function get_show_data_platoons_include_member_count()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);
        $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertEquals(2, $result->platoons->first()->members_count);
    }

    #[Test]
    public function get_show_data_platoons_include_voice_active_count()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $this->createMember([
            'division_id'         => $division->id,
            'platoon_id'          => $platoon->id,
            'last_voice_activity' => Carbon::now()->subDays(5),
        ]);
        $this->createMember([
            'division_id'         => $division->id,
            'platoon_id'          => $platoon->id,
            'last_voice_activity' => Carbon::now()->subDays(60),
        ]);
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertEquals(1, $result->platoons->first()->voice_active_count);
    }

    #[Test]
    public function get_show_data_includes_division_leaders()
    {
        $division = $this->createActiveDivision();
        $co       = $this->createCommander($division);
        $xo       = $this->createExecutiveOfficer($division);
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertCount(2, $result->divisionLeaders);
    }

    #[Test]
    public function get_show_data_includes_pending_actions()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertNotNull($result->pendingActions);
    }

    #[Test]
    public function get_show_data_includes_division_anniversaries()
    {
        $division = $this->createActiveDivision();
        $this->createMember([
            'division_id' => $division->id,
            'join_date'   => Carbon::now()->subYears(1)->subDays(3),
        ]);
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertNotNull($result->divisionAnniversaries);
    }

    #[Test]
    public function get_show_data_includes_previous_census()
    {
        $division = $this->createActiveDivision();
        Census::factory()->create([
            'division_id' => $division->id,
            'count'       => 50,
            'created_at'  => Carbon::now()->subDays(7),
        ]);
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertNotNull($result->previousCensus);
    }

    #[Test]
    public function get_show_data_platoons_load_squads()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertTrue($result->platoons->first()->relationLoaded('squads'));
        $this->assertCount(1, $result->platoons->first()->squads);
    }

    #[Test]
    public function get_show_data_platoons_load_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $this->createPlatoonLeader($platoon);
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $this->actingAs($user);

        $result = $this->service->getShowData($division);

        $this->assertTrue($result->platoons->first()->relationLoaded('leader'));
    }
}
