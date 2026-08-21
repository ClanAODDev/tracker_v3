<?php

namespace Tests\Feature\Services;

use App\Services\CloudflareDnsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class CloudflareDnsServiceTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    protected bool $seed = false;

    private function makeService(array $existingCnames = []): CloudflareDnsService
    {
        $service             = Mockery::mock(CloudflareDnsService::class)->makePartial();
        $service->zoneDomain = 'clanaod.net';
        $service->shouldReceive('isConfigured')->andReturn(true);
        $service->shouldReceive('listCnames')->andReturn(collect($existingCnames));

        return $service;
    }

    #[Test]
    public function preview_sync_reports_no_changes_needed(): void
    {
        $this->createActiveDivision(['name' => 'wow']);

        $service = $this->makeService(['wow' => ['id' => 'rec-1', 'name' => 'wow.clanaod.net']]);

        $this->assertStringContainsString('No changes needed', $service->previewSync()->toHtml());
    }

    #[Test]
    public function preview_sync_lists_records_that_would_be_created(): void
    {
        $this->createActiveDivision(['name' => 'new-game']);

        $service = $this->makeService();

        $html = $service->previewSync()->toHtml();

        $this->assertStringContainsString('Would create', $html);
        $this->assertStringContainsString('new-game.clanaod.net', $html);
    }

    #[Test]
    public function preview_sync_lists_records_that_would_be_deleted(): void
    {
        $this->createInactiveDivision(['name' => 'old-game']);

        $service = $this->makeService(['old-game' => ['id' => 'rec-stale', 'name' => 'old-game.clanaod.net']]);

        $html = $service->previewSync()->toHtml();

        $this->assertStringContainsString('Would delete', $html);
        $this->assertStringContainsString('old-game.clanaod.net', $html);
    }
}
