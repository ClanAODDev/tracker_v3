<?php

namespace Tests\Feature\Reports;

use App\Exceptions\FactoryMissingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ReportsControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_clan_census_report_requires_authentication()
    {
        $response = $this->get(route('reports.clan-census'));

        $response->assertRedirect('/login');
    }

    public function test_clan_census_throws_exception_without_census_data()
    {
        $officer = $this->createOfficer();

        $this->expectException(FactoryMissingException::class);

        $this->withoutExceptionHandling()
            ->actingAs($officer)
            ->get(route('reports.clan-census'));
    }

    public function test_outstanding_inactives_report_displays()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('reports.outstanding-inactives'));

        $response->assertOk();
    }

    public function test_outstanding_inactives_requires_authentication()
    {
        $response = $this->get(route('reports.outstanding-inactives'));

        $response->assertRedirect('/login');
    }

    public function test_leadership_report_displays()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('leadership'));

        $response->assertOk();
    }

    public function test_leadership_report_requires_authentication()
    {
        $response = $this->get(route('leadership'));

        $response->assertRedirect('/login');
    }

    public function test_division_turnover_report_displays_for_admin()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('reports.division-turnover'));

        $response->assertOk();
    }

    public function test_division_turnover_requires_admin_role()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('reports.division-turnover'));

        $response->assertForbidden();
    }
}
