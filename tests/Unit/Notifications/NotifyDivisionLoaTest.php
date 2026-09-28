<?php

namespace Tests\Unit\Notifications;

use App\Models\Division;
use App\Notifications\Channel\NotifyDivisionLoaExpired;
use App\Notifications\Channel\NotifyDivisionLoaExpiring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class NotifyDivisionLoaTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    public static function notifications(): array
    {
        return [
            'expiring' => [NotifyDivisionLoaExpiring::class, 'loa_expiring'],
            'expired'  => [NotifyDivisionLoaExpired::class, 'loa_expired'],
        ];
    }

    #[Test]
    #[DataProvider('notifications')]
    public function it_is_not_sent_when_stored_chat_alerts_predate_the_loa_settings(string $class, string $key): void
    {
        $division = $this->divisionWithChatAlerts(['member_removed' => 'officers']);

        $this->assertFalse((new $class($this->leaves()))->shouldSend($division));
    }

    #[Test]
    #[DataProvider('notifications')]
    public function it_targets_the_configured_channel_when_enabled(string $class, string $key): void
    {
        $division = $this->divisionWithChatAlerts([$key => 'officers']);

        $notification = new $class($this->leaves());

        $this->assertTrue($notification->shouldSend($division));
        $this->assertNotEmpty($notification->toBot($division));
    }

    #[Test]
    public function stored_chat_alerts_fall_back_to_defaults_for_missing_keys(): void
    {
        $division = $this->divisionWithChatAlerts(['member_removed' => 'officers']);

        $this->assertSame('officers', $division->settings()->get('chat_alerts.member_removed'));
        $this->assertFalse($division->settings()->get('chat_alerts.loa_expiring'));
    }

    private function divisionWithChatAlerts(array $chatAlerts): Division
    {
        $division           = $this->createActiveDivision();
        $division->settings = ['chat_alerts' => $chatAlerts];
        $division->save();

        return $division->fresh();
    }

    private function leaves()
    {
        return collect([['name' => 'Rizman', 'reason' => 'Other', 'end_date' => 'Oct 1, 2026']]);
    }
}
