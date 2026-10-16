<?php

namespace Tests\Unit;

use App\Models\Division;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DivisionTest extends TestCase
{
    #[Test]
    public function it_has_a_lowercase_abbreviation()
    {
        $division = Division::factory()->make([
            'abbreviation' => 'UPPERCASE',
        ]);

        $this->assertSame($division->abbreviation, 'uppercase');
    }

    #[Test]
    public function chat_alert_returns_the_configured_channel_and_null_when_unset(): void
    {
        $division = Division::factory()->create();
        $division->settings()->set('chat_alerts.member_awarded', 'officers');

        $this->assertSame('officers', $division->fresh()->chatAlert('member_awarded'));
        $this->assertEmpty($division->fresh()->chatAlert('loa_expiring'));
    }
}
