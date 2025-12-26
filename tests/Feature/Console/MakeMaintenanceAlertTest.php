<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeMaintenanceAlertTest extends TestCase
{
    protected string $alertPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertPath = base_path('maintenance.alert');

        if (File::exists($this->alertPath)) {
            File::delete($this->alertPath);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->alertPath)) {
            File::delete($this->alertPath);
        }
        parent::tearDown();
    }

    public function test_command_shows_status_when_no_options(): void
    {
        $this->artisan('tracker:maintenance-alert')
            ->assertSuccessful()
            ->expectsOutput('No maintenance alert is currently set.')
            ->expectsOutput('Use --set to create an alert or --clear to remove it.');
    }

    public function test_command_sets_alert_message(): void
    {
        $this->artisan('tracker:maintenance-alert --set')
            ->expectsQuestion('What would you like the alert set to? (basic HTML allowed)', 'Test maintenance message')
            ->assertSuccessful()
            ->expectsOutput('Alert set to: Test maintenance message');

        $this->assertTrue(File::exists($this->alertPath));
        $this->assertEquals('Test maintenance message', File::get($this->alertPath));
    }

    public function test_command_clears_alert(): void
    {
        File::put($this->alertPath, 'Existing alert');

        $this->artisan('tracker:maintenance-alert --clear')
            ->assertSuccessful()
            ->expectsOutput('Alert has been cleared.');

        $this->assertFalse(File::exists($this->alertPath));
    }

    public function test_command_clears_nonexistent_alert(): void
    {
        $this->artisan('tracker:maintenance-alert --clear')
            ->assertSuccessful()
            ->expectsOutput('No alert to clear.');
    }

    public function test_command_prompts_before_replacing_existing_alert(): void
    {
        File::put($this->alertPath, 'Existing alert');

        $this->artisan('tracker:maintenance-alert --set')
            ->expectsConfirmation('Alert already exists. Replace it?', 'yes')
            ->expectsQuestion('What would you like the alert set to? (basic HTML allowed)', 'New message')
            ->assertSuccessful()
            ->expectsOutput('Alert set to: New message');

        $this->assertEquals('New message', File::get($this->alertPath));
    }

    public function test_command_does_not_replace_when_declined(): void
    {
        File::put($this->alertPath, 'Existing alert');

        $this->artisan('tracker:maintenance-alert --set')
            ->expectsConfirmation('Alert already exists. Replace it?', 'no')
            ->assertSuccessful();

        $this->assertEquals('Existing alert', File::get($this->alertPath));
    }
}
