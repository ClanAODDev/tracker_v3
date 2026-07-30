<?php

namespace Tests\Feature;

use App\Data\DivisionComparisonData;
use App\Data\MemberStatsData;
use App\Repositories\MemberRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberStatsDataTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function division_comparison_is_null_without_a_division()
    {
        $member = $this->createMember(['division_id' => 0, 'join_date' => now()->subDays(30)]);

        $stats = MemberStatsData::fromMember($member, null, new MemberRepository);

        $this->assertNull($stats->divisionComparison);
    }

    #[Test]
    public function division_comparison_is_populated_with_a_division()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'join_date' => now()->subDays(100)]);
        $this->createMember(['division_id' => $division->id, 'join_date' => now()->subDays(50)]);

        $stats = MemberStatsData::fromMember($member, $division, new MemberRepository);

        $this->assertInstanceOf(DivisionComparisonData::class, $stats->divisionComparison);
    }
}
