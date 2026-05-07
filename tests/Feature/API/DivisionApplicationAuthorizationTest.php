<?php

namespace Tests\Feature\API;

use App\Models\DivisionApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionApplicationAuthorizationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function officer_cannot_view_application_belonging_to_another_division()
    {
        $officer     = $this->createOfficer();
        $otherDiv    = $this->createActiveDivision();
        $application = DivisionApplication::factory()->forDivision($otherDiv)->create();

        $this->actingAs($officer)
            ->getJson(route('division-applications.show', [$officer->member->division->slug, $application->id]))
            ->assertForbidden();
    }

    #[Test]
    public function officer_can_view_application_belonging_to_their_division()
    {
        $officer     = $this->createOfficer();
        $division    = $officer->member->division;
        $application = DivisionApplication::factory()->forDivision($division)->create();

        $this->actingAs($officer)
            ->getJson(route('division-applications.show', [$division->slug, $application->id]))
            ->assertOk();
    }

    #[Test]
    public function sr_ldr_cannot_delete_application_from_another_division()
    {
        $srLdr       = $this->createSeniorLeader();
        $otherDiv    = $this->createActiveDivision();
        $application = DivisionApplication::factory()->forDivision($otherDiv)->create();

        $this->actingAs($srLdr)
            ->deleteJson(route('division-applications.destroy', [$srLdr->member->division->slug, $application->id]))
            ->assertForbidden();
    }

    #[Test]
    public function officer_cannot_comment_on_application_from_another_division()
    {
        $officer     = $this->createOfficer();
        $otherDiv    = $this->createActiveDivision();
        $application = DivisionApplication::factory()->forDivision($otherDiv)->create();

        $this->actingAs($officer)
            ->postJson(route('division-applications.comments.store', [$officer->member->division->slug, $application->id]), [
                'body' => 'This is a test comment',
            ])->assertForbidden();
    }
}
