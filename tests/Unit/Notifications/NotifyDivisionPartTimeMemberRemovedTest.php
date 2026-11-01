<?php

namespace Tests\Unit\Notifications;

use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use App\Notifications\Channel\NotifyDivisionPartTimeMemberRemoved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NotifyDivisionPartTimeMemberRemovedTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_long_removal_reason_is_trimmed_to_fit_discord(): void
    {
        $primary  = $this->createActiveDivision();
        $partTime = $this->createActiveDivision();
        $partTime->settings()->set('chat_alerts.pt_member_removed', 'officers');
        $primary->settings()->set('chat_alerts.member_removed', 'officers');
        $member = $this->createMember(['division_id' => $primary->id]);
        $reason = "Didn't meet the activity requirement. " . str_repeat('x', 2000);

        $partTimeFields = (new NotifyDivisionPartTimeMemberRemoved($member, $reason))->toBot($partTime)['body']['embeds'][0]['fields'];
        $primaryFields  = (new NotifyDivisionMemberRemoved($member, null, $reason))->toBot($primary)['body']['embeds'][0]['fields'];

        foreach ([$partTimeFields, $primaryFields] as $fields) {
            $value = collect($fields)->firstWhere('name', 'Reason')['value'];

            $this->assertSame(1024, mb_strlen($value));
            $this->assertStringStartsWith("Didn't meet the activity requirement.", $value);
            $this->assertStringEndsWith('…', $value);
        }
    }

    #[Test]
    public function a_short_removal_reason_is_sent_unchanged(): void
    {
        $partTime = $this->createActiveDivision();
        $partTime->settings()->set('chat_alerts.pt_member_removed', 'officers');
        $member = $this->createMember(['division_id' => $this->createActiveDivision()->id]);

        $fields = (new NotifyDivisionPartTimeMemberRemoved($member, "Didn't show up"))->toBot($partTime)['body']['embeds'][0]['fields'];

        $this->assertSame("Didn't show up", collect($fields)->firstWhere('name', 'Reason')['value']);
    }
}
