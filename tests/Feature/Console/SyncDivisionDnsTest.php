<?php

namespace Tests\Feature\Console;

use App\Jobs\SyncDivisionDns;
use App\Services\CloudflareDnsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class SyncDivisionDnsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesDivisions;

    protected bool $seed = false;

    private function makeService(array $existingCnames = []): CloudflareDnsService
    {
        $service = Mockery::mock(CloudflareDnsService::class);
        $service->shouldReceive('listCnames')->andReturn(collect($existingCnames));

        return $service;
    }

    #[Test]
    public function creates_cnames_for_active_divisions(): void
    {
        $this->createActiveDivision(['name' => 'wow']);
        $this->createActiveDivision(['name' => 'ps2-tracker']);

        $service = $this->makeService();
        $service->shouldReceive('createCname')->with('wow')->once();
        $service->shouldReceive('createCname')->with('ps2-tracker')->once();

        (new SyncDivisionDns)->handle($service);
    }

    #[Test]
    public function uses_dns_subdomain_over_slug_when_set(): void
    {
        $this->createActiveDivision(['name' => 'ps2-tracker', 'dns_subdomain' => 'ps2']);

        $service = $this->makeService();
        $service->shouldReceive('createCname')->with('ps2')->once();
        $service->shouldReceive('createCname')->with('ps2-tracker')->never();

        (new SyncDivisionDns)->handle($service);
    }

    #[Test]
    public function skips_divisions_that_already_have_a_cname(): void
    {
        $this->createActiveDivision(['name' => 'wow']);

        $service = $this->makeService(['wow' => ['id' => 'rec-1', 'name' => 'wow.clanaod.net']]);
        $service->shouldReceive('createCname')->never();
        $service->shouldReceive('deleteCname')->never();

        (new SyncDivisionDns)->handle($service);
    }

    #[Test]
    public function deletes_stale_cnames_for_shutdown_divisions(): void
    {
        $this->createInactiveDivision(['name' => 'old-game']);

        $service = $this->makeService(['old-game' => ['id' => 'rec-stale', 'name' => 'old-game.clanaod.net']]);
        $service->shouldReceive('deleteCname')->with('rec-stale')->once();

        (new SyncDivisionDns)->handle($service);
    }

    #[Test]
    public function never_deletes_protected_subdomains(): void
    {
        $existing = [];
        foreach (CloudflareDnsService::PROTECTED_SUBDOMAINS as $subdomain) {
            $existing[$subdomain] = ['id' => "rec-{$subdomain}", 'name' => "{$subdomain}.clanaod.net"];
        }

        $service = $this->makeService($existing);
        $service->shouldReceive('deleteCname')->never();

        (new SyncDivisionDns)->handle($service);
    }

    #[Test]
    public function dry_run_skips_create_and_delete_calls(): void
    {
        $this->createActiveDivision(['name' => 'new-game']);

        $service = $this->makeService(['stale' => ['id' => 'rec-stale', 'name' => 'stale.clanaod.net']]);
        $service->shouldReceive('createCname')->never();
        $service->shouldReceive('deleteCname')->never();

        (new SyncDivisionDns(dryRun: true))->handle($service);
    }

    #[Test]
    public function dry_run_logs_with_prefix(): void
    {
        Log::spy();

        $this->createActiveDivision(['name' => 'wow']);

        $service = $this->makeService();
        $service->shouldReceive('createCname')->never();

        (new SyncDivisionDns(dryRun: true))->handle($service);

        Log::shouldHaveReceived('info')
            ->with(Mockery::pattern('/^\[DRY RUN\]/'), Mockery::any())
            ->atLeast()->once();
    }

    #[Test]
    public function logs_summary_on_completion(): void
    {
        Log::spy();

        $this->createActiveDivision(['name' => 'wow']);

        $service = $this->makeService();
        $service->shouldReceive('createCname')->once();

        (new SyncDivisionDns)->handle($service);

        Log::shouldHaveReceived('info')
            ->with('DNS sync complete', Mockery::on(fn ($ctx) => isset($ctx['created']) && in_array('wow', $ctx['created'])))
            ->once();
    }

    #[Test]
    public function expected_subdomains_excludes_shutdown_divisions(): void
    {
        $this->createActiveDivision(['name' => 'wow']);
        $this->createInactiveDivision(['name' => 'retired']);

        $subdomains = SyncDivisionDns::expectedSubdomains();

        $this->assertTrue($subdomains->contains('wow'));
        $this->assertFalse($subdomains->contains('retired'));
    }

    #[Test]
    public function expected_subdomains_excludes_inactive_divisions(): void
    {
        $this->createActiveDivision(['name' => 'wow']);
        $this->createDivision(['name' => 'inactive-game', 'active' => false]);

        $subdomains = SyncDivisionDns::expectedSubdomains();

        $this->assertTrue($subdomains->contains('wow'));
        $this->assertFalse($subdomains->contains('inactive-game'));
    }

    #[Test]
    public function expected_subdomains_excludes_dns_excluded_slugs(): void
    {
        $this->createActiveDivision(['name' => 'wow']);

        foreach (CloudflareDnsService::DNS_EXCLUDED_SLUGS as $slug) {
            $this->createActiveDivision(['name' => $slug]);
        }

        $subdomains = SyncDivisionDns::expectedSubdomains();

        $this->assertTrue($subdomains->contains('wow'));
        foreach (CloudflareDnsService::DNS_EXCLUDED_SLUGS as $slug) {
            $this->assertFalse($subdomains->contains($slug));
        }
    }

    #[Test]
    public function handle_returns_created_and_deleted_arrays(): void
    {
        $this->createActiveDivision(['name' => 'new-game']);

        $service = $this->makeService(['stale' => ['id' => 'rec-stale', 'name' => 'stale.clanaod.net']]);
        $service->shouldReceive('createCname')->once();
        $service->shouldReceive('deleteCname')->with('rec-stale')->once();

        $result = (new SyncDivisionDns)->handle($service);

        $this->assertSame(['new-game'], $result['created']);
        $this->assertSame(['stale'], $result['deleted']);
    }
}
