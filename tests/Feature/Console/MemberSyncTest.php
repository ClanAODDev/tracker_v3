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
        $mockService->shouldReceive('sync')->andReturn(true);

        $this->app->instance(MemberSyncService::class, $mockService);

        $this->artisan('tracker:member-sync')
            ->assertSuccessful();
    }

    public function test_command_fails_when_no_data_available(): void
    {
        $mockService = Mockery::mock(MemberSyncService::class);
        $mockService->shouldReceive('onUpdate')->andReturnSelf();
        $mockService->shouldReceive('onAdd')->andReturnSelf();
        $mockService->shouldReceive('sync')->andReturn(false);

        $this->app->instance(MemberSyncService::class, $mockService);

        $this->artisan('tracker:member-sync')
            ->assertFailed()
            ->expectsOutput('Member sync failed - no data available from forum');
    }
}
