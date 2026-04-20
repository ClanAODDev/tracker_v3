<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BotCommandControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-bot-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.bot_cmd_tokens' => $this->token]);
    }

    private function botGet(string $command, array $params = []): TestResponse
    {
        return $this->getJson(route('bot.commands', ['command' => $command]) . '?' . http_build_query(
            array_merge(['token' => $this->token], $params)
        ));
    }

    public function test_bot_command_requires_valid_token()
    {
        $this->getJson(route('bot.commands', ['command' => 'reports']) . '?token=invalid&value=sgt-training')
            ->assertStatus(401);
    }

    public function test_unknown_command_returns_unrecognized_message()
    {
        $response = $this->botGet('nonexistent-command');

        $response->assertOk()
            ->assertJson(['message' => 'Unrecognized command. Sorry!']);
    }

    public function test_reports_command_requires_value()
    {
        $this->botGet('reports')
            ->assertStatus(422);
    }

    public function test_reports_command_returns_not_found_for_unknown_report()
    {
        $response = $this->botGet('reports', ['value' => 'nonexistent-report']);

        $response->assertOk()
            ->assertJson(['message' => "Report 'nonexistent-report' not found."]);
    }

    public function test_sgt_training_report_returns_message_with_table()
    {
        $division = Division::factory()->create(['active' => true]);

        $ssgt = Member::factory()->create([
            'rank'        => Rank::STAFF_SERGEANT,
            'division_id' => $division->id,
        ]);

        Member::factory()->count(3)->create([
            'division_id'     => $division->id,
            'last_trained_by' => $ssgt->clan_id,
            'last_trained_at' => now(),
        ]);

        $response = $this->botGet('reports', ['value' => 'sgt-training']);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        $message = $response->json('message');
        $this->assertStringContainsString('```', $message);
        $this->assertStringContainsString('SSgt', $message);
        $this->assertStringContainsString('Trainings', $message);
        $this->assertStringContainsString($ssgt->name, $message);
        $this->assertStringContainsString('3', $message);
    }

    public function test_sgt_training_report_returns_message_when_no_ssgts()
    {
        $response = $this->botGet('reports', ['value' => 'sgt-training']);

        $response->assertOk();
        $this->assertStringContainsString('No SSgts found.', $response->json('message'));
    }
}
