<?php

namespace Tests\Unit\Notifications;

use App\Enums\Rank;
use App\Models\Division;
use App\Notifications\Channel\NotifyDivisionMemberTransferRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NotifyDivisionMemberTransferRequestedTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function withTransferAlertsEnabled(Division $division): Division
    {
        $division->settings = array_merge($division->settings, [
            'chat_alerts' => array_merge($division->settings['chat_alerts'], ['member_transferred' => 'officers']),
        ]);
        $division->save();

        return $division;
    }

    private function messageValue($notification, $notifiable): string
    {
        $message = $notification->toBot($notifiable);

        return $message['body']['embeds'][0]['fields'][0]['value'];
    }

    #[Test]
    public function auto_approved_outgoing_notice_names_the_division_the_member_went_to(): void
    {
        $oldDivision = $this->withTransferAlertsEnabled($this->createActiveDivision(['name' => 'Call of Duty']));
        $member      = $this->createMember(['division_id' => $oldDivision->id, 'rank' => Rank::PRIVATE]);

        $notification = new NotifyDivisionMemberTransferRequested(
            $member,
            'Battlefield',
            NotifyDivisionMemberTransferRequested::TYPE_OUTGOING,
            autoApproved: true,
        );

        $value = $this->messageValue($notification, $oldDivision);

        $this->assertStringContainsString('has transferred to Battlefield', $value);
        $this->assertStringNotContainsString('has transferred from', $value);
    }

    #[Test]
    public function auto_approved_incoming_notice_names_the_division_the_member_came_from(): void
    {
        $newDivision = $this->withTransferAlertsEnabled($this->createActiveDivision(['name' => 'Battlefield']));
        $member      = $this->createMember(['division_id' => $newDivision->id, 'rank' => Rank::PRIVATE]);

        $notification = new NotifyDivisionMemberTransferRequested(
            $member,
            'Call of Duty',
            NotifyDivisionMemberTransferRequested::TYPE_INCOMING,
            autoApproved: true,
        );

        $value = $this->messageValue($notification, $newDivision);

        $this->assertStringContainsString('has transferred from Call of Duty', $value);
        $this->assertStringNotContainsString('has transferred to', $value);
    }

    #[Test]
    public function pending_outgoing_notice_names_the_division_the_member_is_requesting_to_join(): void
    {
        $oldDivision = $this->withTransferAlertsEnabled($this->createActiveDivision(['name' => 'Call of Duty']));
        $member      = $this->createMember(['division_id' => $oldDivision->id, 'rank' => Rank::SERGEANT]);

        $notification = new NotifyDivisionMemberTransferRequested(
            $member,
            'Battlefield',
            NotifyDivisionMemberTransferRequested::TYPE_OUTGOING,
        );

        $value = $this->messageValue($notification, $oldDivision);

        $this->assertStringContainsString('transfer request for', $value);
        $this->assertStringContainsString('to Battlefield has been created', $value);
    }

    #[Test]
    public function pending_incoming_notice_names_the_division_the_member_is_requesting_from(): void
    {
        $newDivision = $this->withTransferAlertsEnabled($this->createActiveDivision(['name' => 'Battlefield']));
        $member      = $this->createMember(['division_id' => $newDivision->id, 'rank' => Rank::SERGEANT]);

        $notification = new NotifyDivisionMemberTransferRequested(
            $member,
            'Call of Duty',
            NotifyDivisionMemberTransferRequested::TYPE_INCOMING,
        );

        $value = $this->messageValue($notification, $newDivision);

        $this->assertStringContainsString('transfer request for', $value);
        $this->assertStringContainsString('from Call of Duty has been created', $value);
    }
}
