<?php

namespace Tests\Feature\Bot;

use App\Enums\Rank;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class OutstandingTicketsReportTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('aod.bot_cmd_tokens', 'a-test-token');
    }

    private function runReport(?string $discordId)
    {
        return $this->json('GET', '/bot/commands/reports', array_filter([
            'token'      => 'a-test-token',
            'value'      => 'outstanding-tickets',
            'discord_id' => $discordId,
        ]));
    }

    private function caller(Rank $rank): string
    {
        $this->createMember([
            'division_id' => $this->createActiveDivision()->id,
            'rank'        => $rank,
            'discord_id'  => '700000000000000001',
        ]);

        return '700000000000000001';
    }

    #[Test]
    public function it_lists_open_tickets_oldest_first_with_owner_date_and_link()
    {
        $older = Ticket::factory()->assigned()->create(['created_at' => now()->subDays(10)]);
        $newer = Ticket::factory()->create(['created_at' => now()->subDay()]);
        Ticket::factory()->resolved()->create();

        $embed = $this->runReport($this->caller(Rank::MASTER_SERGEANT))->assertOk()->json('embed');

        $this->assertSame('Outstanding tickets (2)', $embed['title']);

        $lines = explode("\n", $embed['description']);
        $this->assertCount(2, $lines);
        $this->assertStringContainsString("[#{$older->id}](" . route('help.tickets.show', $older) . ')', $lines[0]);
        $this->assertStringContainsString($older->owner->name, $lines[0]);
        $this->assertStringContainsString("<t:{$older->created_at->timestamp}:R>", $lines[0]);
        $this->assertStringContainsString("[#{$newer->id}]", $lines[1]);
        $this->assertStringContainsString('*Unassigned*', $lines[1]);
    }

    #[Test]
    public function callers_below_master_sergeant_are_refused()
    {
        Ticket::factory()->create();

        $this->runReport($this->caller(Rank::STAFF_SERGEANT))
            ->assertOk()
            ->assertJsonPath('message', 'This report is limited to MSgt and above.')
            ->assertJsonMissingPath('embed');
    }

    #[Test]
    public function an_unknown_caller_is_refused()
    {
        Ticket::factory()->create();

        $this->runReport(null)->assertJsonPath('message', 'This report is limited to MSgt and above.');
        $this->runReport('123')->assertJsonPath('message', 'This report is limited to MSgt and above.');
    }

    #[Test]
    public function it_says_so_when_nothing_is_outstanding()
    {
        Ticket::factory()->resolved()->create();

        $this->runReport($this->caller(Rank::SERGEANT_MAJOR))->assertJsonPath('message', 'No outstanding tickets.');
    }

    #[Test]
    public function text_reports_are_still_wrapped_in_a_code_block()
    {
        $message = $this->json('GET', '/bot/commands/reports', ['token' => 'a-test-token', 'value' => 'sgt-training'])
            ->assertOk()
            ->json('message');

        $this->assertStringStartsWith("```\n", $message);
    }
}
