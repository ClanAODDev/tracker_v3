<?php

namespace Tests\Unit\Services;

use App\Enums\Rank;
use App\Services\RankTimelineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankTimelineServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private RankTimelineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RankTimelineService;
    }

    public function test_build_timeline_returns_object_with_required_properties()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('nodes', $result);
        $this->assertObjectHasProperty('historyItems', $result);
        $this->assertObjectHasProperty('hasHistory', $result);
    }

    public function test_build_timeline_has_history_is_true_when_join_date_exists()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $this->assertTrue($result->hasHistory);
    }

    public function test_build_timeline_has_history_is_true_when_rank_history_exists()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::SPECIALIST,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subYear()),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $this->assertTrue($result->hasHistory);
    }

    public function test_build_timeline_has_history_is_false_when_no_history()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => null,
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $this->assertFalse($result->hasHistory);
    }

    public function test_build_timeline_creates_join_node()
    {
        $division = $this->createActiveDivision();
        $joinDate = Carbon::now()->subYears(2);
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => $joinDate,
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $joinNode = $result->nodes->first();
        $this->assertEquals('join', $joinNode->type);
        $this->assertEquals('Joined AOD', $joinNode->label);
        $this->assertEquals($joinDate->format('M Y'), $joinNode->date);
    }

    public function test_build_timeline_creates_promotion_nodes_for_rank_history()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::CORPORAL,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subYear()),
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subMonths(6)),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $promotionNodes = $result->nodes->filter(fn ($n) => $n->type === 'promotion');
        $this->assertGreaterThanOrEqual(1, $promotionNodes->count());
    }

    public function test_build_timeline_filters_demotions_from_progression_nodes()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::CORPORAL,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subYears(1)->subMonths(6)),
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subYear()),
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subMonths(6)),
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subMonths(3)),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $promotionNodes = $result->nodes->filter(fn ($n) => $n->type === 'promotion');
        $this->assertLessThan(4, $promotionNodes->count());
    }

    public function test_build_timeline_history_items_include_demotions()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subYear()),
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subMonths(6)),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $demotions = $result->historyItems->filter(fn ($i) => $i->type === 'demotion');
        $this->assertCount(1, $demotions);
    }

    public function test_build_timeline_nodes_alternate_positions()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::SERGEANT,
            'join_date' => Carbon::now()->subYears(3),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::PRIVATE_FIRST_CLASS, Carbon::now()->subYears(2)),
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subYear()),
            $this->createRankEntry(Rank::SERGEANT, Carbon::now()->subMonths(6)),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $positions = $result->nodes->pluck('position')->toArray();
        $this->assertEquals('left', $positions[0]);
        $this->assertEquals('right', $positions[1]);
        $this->assertEquals('left', $positions[2]);
        $this->assertEquals('right', $positions[3]);
    }

    public function test_build_timeline_history_items_include_join_event()
    {
        $division = $this->createActiveDivision();
        $joinDate = Carbon::now()->subYears(2);
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::CORPORAL,
            'join_date' => $joinDate,
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $joinItems = $result->historyItems->filter(fn ($i) => $i->type === 'join');
        $this->assertCount(1, $joinItems);
        $this->assertEquals($joinDate->format('M j, Y'), $joinItems->first()->date);
    }

    public function test_build_timeline_returns_nodes_as_collection()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => Carbon::now()->subYears(1),
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result->nodes);
    }

    public function test_build_timeline_returns_history_items_as_collection()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
            'join_date' => Carbon::now()->subYears(1),
        ]);

        $result = $this->service->buildTimeline($member, collect());

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result->historyItems);
    }

    public function test_build_timeline_promotion_node_has_rank_abbreviation()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::CORPORAL,
            'join_date' => Carbon::now()->subYears(2),
        ]);

        $rankHistory = collect([
            $this->createRankEntry(Rank::CORPORAL, Carbon::now()->subYear()),
        ]);

        $result = $this->service->buildTimeline($member, $rankHistory);

        $promotionNode = $result->nodes->filter(fn ($n) => $n->type === 'promotion')->first();
        $this->assertEquals(Rank::CORPORAL->getAbbreviation(), $promotionNode->rank);
    }

    private function createRankEntry(Rank $rank, Carbon $createdAt): object
    {
        return (object) [
            'rank' => $rank,
            'created_at' => $createdAt,
        ];
    }
}
