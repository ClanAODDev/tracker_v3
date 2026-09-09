<?php

namespace Tests\Feature\Reports;

use App\Exceptions\FactoryMissingException;
use App\Models\Census;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ReportsControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function clan_census_report_requires_authentication()
    {
        $this->get(route('reports.clan-census'))->assertRedirect('/login');
    }

    #[Test]
    public function clan_census_throws_exception_without_census_data()
    {
        $officer = $this->createOfficer();

        $this->expectException(FactoryMissingException::class);

        $this->withoutExceptionHandling()
            ->actingAs($officer)
            ->get(route('reports.clan-census'));
    }

    #[Test]
    public function clan_census_report_renders_its_inertia_page()
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();
        Census::factory()->count(3)->create(['division_id' => $division->id]);

        $this->actingAs($officer)
            ->get(route('reports.clan-census'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/clan-census')
                ->has('chart')
                ->has('censusTable')
                ->has('rankDemographic'));
    }

    #[Test]
    public function outstanding_inactives_report_renders_its_inertia_page()
    {
        $officer = $this->createOfficer();

        $this->actingAs($officer)
            ->get(route('reports.outstanding-inactives'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/outstanding')
                ->has('divisions')
                ->has('totals'));
    }

    #[Test]
    public function outstanding_inactives_requires_authentication()
    {
        $this->get(route('reports.outstanding-inactives'))->assertRedirect('/login');
    }

    #[Test]
    public function leadership_report_renders_its_inertia_page()
    {
        $officer = $this->createOfficer();

        $this->actingAs($officer)
            ->get(route('leadership'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/leadership')
                ->has('clanLeadership')
                ->has('divisions'));
    }

    #[Test]
    public function leadership_report_requires_authentication()
    {
        $this->get(route('leadership'))->assertRedirect('/login');
    }

    #[Test]
    public function division_turnover_report_renders_for_admin()
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('reports.division-turnover'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/turnover')
                ->has('divisions')
                ->has('totals'));
    }

    #[Test]
    public function division_turnover_requires_admin_role()
    {
        $officer = $this->createOfficer();

        $this->actingAs($officer)
            ->get(route('reports.division-turnover'))
            ->assertForbidden();
    }
}
