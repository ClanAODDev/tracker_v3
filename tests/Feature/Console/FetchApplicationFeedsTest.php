<?php

namespace Tests\Feature\Console;

use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchApplicationFeedsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_succeeds_with_no_divisions(): void
    {
        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();
    }

    public function test_command_skips_divisions_without_rss_feed(): void
    {
        Division::factory()->create();

        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();
    }

    public function test_command_processes_rss_feed(): void
    {
        Http::fake([
            '*' => Http::response($this->sampleRssFeed(), 200),
        ]);

        $division = Division::factory()->create();
        $division->settings()->set('recruitment_rss_feed', 'https://example.com/rss.xml');

        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();

        $this->assertTrue(Cache::has('application_item_12345'));
    }

    public function test_command_skips_cached_items(): void
    {
        Http::fake([
            '*' => Http::response($this->sampleRssFeed(), 200),
        ]);

        Cache::put('application_item_12345', ['guid' => '12345'], now()->addHour());

        $division = Division::factory()->create();
        $division->settings()->set('recruitment_rss_feed', 'https://example.com/rss.xml');

        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();
    }

    public function test_fresh_option_clears_cache(): void
    {
        Http::fake([
            '*' => Http::response($this->sampleRssFeed(), 200),
        ]);

        Cache::put('application_item_12345', ['guid' => '12345'], now()->addHour());

        $division = Division::factory()->create();
        $division->settings()->set('recruitment_rss_feed', 'https://example.com/rss.xml');

        $this->artisan('tracker:fetch-applications --fresh')
            ->assertSuccessful();

        $cached = Cache::get('application_item_12345');
        $this->assertNotNull($cached);
        $this->assertArrayHasKey('pub_date', $cached);
    }

    public function test_command_skips_excluded_divisions(): void
    {
        config(['tracker.excluded_divisions' => ['Test Division']]);

        $division = Division::factory()->create(['name' => 'Test Division']);
        $division->settings()->set('recruitment_rss_feed', 'https://example.com/rss.xml');

        Http::fake();

        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_command_ignores_invalid_rss_feeds(): void
    {
        Http::fake([
            '*' => Http::response('<html><body>Not RSS</body></html>', 200),
        ]);

        $division = Division::factory()->create();
        $division->settings()->set('recruitment_rss_feed', 'https://example.com/not-rss.html');

        $this->artisan('tracker:fetch-applications')
            ->assertSuccessful();

        $this->assertFalse(Cache::has('application_item_12345'));
    }

    protected function sampleRssFeed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Test Feed</title>
        <item>
            <title>New Application</title>
            <link>https://example.com/forum/thread.php?t=12345</link>
            <guid>https://example.com/forum/thread.php?t=12345</guid>
        </item>
    </channel>
</rss>
XML;
    }
}
