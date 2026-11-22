<?php

namespace Tests\Feature;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Jobs\PurgeSiteDivisionCache;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PurgeSiteDivisionCacheTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private const URL = 'https://site.test/webhooks/tracker/divisions';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.aod_site.division_webhook_url' => self::URL,
            'services.aod_site.webhook_secret'       => 'shared-secret',
        ]);
    }

    #[Test]
    public function saving_division_settings_queues_a_site_cache_purge(): void
    {
        Queue::fake();
        Filament::setCurrentPanel('mod');

        $division = $this->createActiveDivision();
        $this->actingAs($this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], ['role' => Role::OFFICER]));

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        Queue::assertPushed(PurgeSiteDivisionCache::class, fn ($job) => $job->slug === $division->slug);
    }

    #[Test]
    public function the_purge_sends_the_slug_signed_with_the_shared_secret(): void
    {
        Http::fake([self::URL => Http::response(null, 204)]);

        (new PurgeSiteDivisionCache('battlefield'))->handle();

        Http::assertSent(function (Request $request) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->body(), 'shared-secret');

            return $request->url() === self::URL
                && $request['slug'] === 'battlefield'
                && $request->header(PurgeSiteDivisionCache::SIGNATURE_HEADER)[0] === $expected;
        });
    }

    #[Test]
    public function the_purge_does_nothing_until_the_site_is_configured(): void
    {
        Http::fake();
        config(['services.aod_site.webhook_secret' => null]);

        (new PurgeSiteDivisionCache('battlefield'))->handle();

        Http::assertNothingSent();
    }

    #[Test]
    public function a_site_outage_is_retried(): void
    {
        Http::fake([self::URL => Http::response(null, 503)]);

        $this->expectException(RequestException::class);

        (new PurgeSiteDivisionCache('battlefield'))->handle();
    }
}
