<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\Channel\NotifyDivisionNewExternalRecruit;
use App\Notifications\Channel\NotifyDivisionNewMemberRecruited;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NotifyDivisionNewMemberRecruitedTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_builds_from_the_notified_division_when_the_member_has_since_lost_theirs(): void
    {
        $division = $this->createActiveDivision();
        $division->settings()->set('chat_alerts.member_created', 'officers');

        $recruiterMember = $this->createMember(['division_id' => $division->id]);
        $recruiter       = User::factory()->create(['member_id' => $recruiterMember->id]);
        $recruit         = $this->createMember(['division_id' => 0, 'unit_id' => null]);

        foreach ([NotifyDivisionNewMemberRecruited::class, NotifyDivisionNewExternalRecruit::class] as $class) {
            $fields = (new $class($recruit, $recruiter))->toBot($division)['body']['embeds'][0]['fields'];

            $this->assertSame('Unassigned', $fields[1]['value']);
        }
    }
}
