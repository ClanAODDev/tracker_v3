<?php

namespace Tests\Feature\Console;

use App\Services\MemberSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MemberSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_succeeds_when_sync_service_succeeds(): void
    {
        $mockService = Mockery::mock(MemberSyncService::class);
        $mockService->shouldReceive('onUpdate')->andReturnSelf();
        $mockService->shouldReceive('onAdd')->andReturnSelf();
        $mockService->shouldReceive('onRemove')->andReturnSelf();
        $mockService->shouldReceive('sync')->andReturn(true);
        $mockService->shouldReceive('getStats')->andReturn([
            'added' => 0,
            'updated' => 0,
            'removed' => 0,
            'errors' => 0,
        ]);

        $this->app->instance(MemberSyncService::class, $mockService);

        $this->artisan('tracker:member-sync')
            ->assertSuccessful()
            ->expectsOutputToContain('Sync complete');
    }

    public function test_command_fails_when_no_data_available(): void
    {
        $mockService = Mockery::mock(MemberSyncService::class);
        $mockService->shouldReceive('onUpdate')->andReturnSelf();
        $mockService->shouldReceive('onAdd')->andReturnSelf();
        $mockService->shouldReceive('onRemove')->andReturnSelf();
        $mockService->shouldReceive('sync')->andReturn(false);
        $mockService->shouldReceive('getLastError')->andReturn(null);

        $this->app->instance(MemberSyncService::class, $mockService);

        $this->artisan('tracker:member-sync')
            ->assertFailed()
            ->expectsOutput('Member sync failed - No data available from forum');
    }

    public function test_command_shows_error_details_on_failure(): void
    {
        $mockService = Mockery::mock(MemberSyncService::class);
        $mockService->shouldReceive('onUpdate')->andReturnSelf();
        $mockService->shouldReceive('onAdd')->andReturnSelf();
        $mockService->shouldReceive('onRemove')->andReturnSelf();
        $mockService->shouldReceive('sync')->andReturn(false);
        $mockService->shouldReceive('getLastError')->andReturn('Connection timeout');

        $this->app->instance(MemberSyncService::class, $mockService);

        $this->artisan('tracker:member-sync')
            ->assertFailed()
            ->expectsOutput('Member sync failed - Connection timeout');
    }
}
