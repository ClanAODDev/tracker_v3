<?php

namespace Tests\Unit\Notifications;

use App\Models\Division;
use App\Notifications\Channel\NotifyDivisionMemberPromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class NotifyDivisionMemberPromotionTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function a_disabled_promotion_alert_is_not_sent(): void
    {
        $division = $this->divisionWithChatAlerts(['member_promoted' => false]);

        $this->assertFalse((new NotifyDivisionMemberPromotion('Member', 'Specialist'))->shouldSend($division));
    }

    #[Test]
    public function a_configured_channel_enables_the_promotion_alert(): void
    {
        $division = $this->divisionWithChatAlerts(['member_promoted' => 'officers']);

        $this->assertTrue((new NotifyDivisionMemberPromotion('Member', 'Specialist'))->shouldSend($division));
    }

    private function divisionWithChatAlerts(array $chatAlerts): Division
    {
        $division           = $this->createActiveDivision();
        $division->settings = ['chat_alerts' => $chatAlerts];
        $division->save();

        return $division->fresh();
    }
}
