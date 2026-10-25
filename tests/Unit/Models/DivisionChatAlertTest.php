<?php

namespace Tests\Unit\Models;

use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisionChatAlertTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function chat_alert_returns_the_configured_channel_and_empty_when_unset(): void
    {
        $division = Division::factory()->create();
        $division->settings()->set('chat_alerts.member_awarded', 'officers');

        $this->assertSame('officers', $division->fresh()->chatAlert('member_awarded'));
        $this->assertEmpty($division->fresh()->chatAlert('loa_expiring'));
    }
}
