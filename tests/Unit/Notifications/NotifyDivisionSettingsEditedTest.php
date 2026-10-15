<?php

namespace Tests\Unit\Notifications;

use App\Models\Division;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionSettingsEdited;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class NotifyDivisionSettingsEditedTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function an_unset_alert_setting_means_disabled(): void
    {
        $division = $this->divisionWithChatAlerts(['division_edited' => null]);

        $this->assertFalse((new NotifyDivisionSettingsEdited(User::factory()->make()))->shouldSend($division));
    }

    #[Test]
    public function a_configured_channel_enables_the_alert(): void
    {
        $division = $this->divisionWithChatAlerts(['division_edited' => 'officers']);

        $this->assertTrue((new NotifyDivisionSettingsEdited(User::factory()->make()))->shouldSend($division));
    }

    private function divisionWithChatAlerts(array $chatAlerts): Division
    {
        $division           = $this->createActiveDivision();
        $division->settings = ['chat_alerts' => $chatAlerts];
        $division->save();

        return $division->fresh();
    }
}
