<?php

namespace Tests\Feature\Reports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionReportControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_census_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.census', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_retention_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.retention-report', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_voice_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.voice-report', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_promotions_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.promotions', $division->slug));

        $response->assertRedirect('/login');
    }
}
