<?php

namespace Tests\Unit\Bot\Reports;

use App\Enums\Rank;
use App\Models\Bot\Reports\SgtTraining;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SgtTrainingTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function report_includes_a_title_above_the_table()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['rank' => Rank::STAFF_SERGEANT, 'division_id' => $division->id]);

        $report = (new SgtTraining)->handle();

        $this->assertStringStartsWith("Number of SGT trainings per SSgt\n\n", $report);
        $this->assertStringContainsString('SSgt', $report);
        $this->assertStringContainsString('Trainings', $report);
    }

    #[Test]
    public function report_shows_fallback_message_when_no_ssgts_exist()
    {
        $report = (new SgtTraining)->handle();

        $this->assertSame('No SSgts found.', $report);
    }
}
