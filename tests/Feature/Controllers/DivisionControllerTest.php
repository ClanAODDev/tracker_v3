<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_show_displays_division_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $response = $this->actingAs($officer)
            ->get(route('division', $division->slug));

        $response->assertOk();
    }

    public function test_show_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division', $division->slug));

        $response->assertRedirect('/login');
    }
}
