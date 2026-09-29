<?php

namespace Tests\Feature\Authorization;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\DivisionTag;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;

class EndpointMatrixTest extends PermissionMatrixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Notification::fake();
        Queue::fake();
    }

    #[Test]
    public function division_application_api_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $divisions = ['division A' => $this->world->divisionA, 'division B' => $this->world->divisionB];

        foreach ($divisions as $division) {
            $division->settings()->set('application_required', true);
        }

        $lines = [];

        foreach ($divisions as $label => $division) {
            $application = $this->application($division);

            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line('GET index', $label, $actor, (string) $this->asActor($actor)->getJson(route('division-applications.index', $division->slug))->status());
            }

            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line('GET show', $label, $actor, (string) $this->asActor($actor)->getJson(route('division-applications.show', [$division->slug, $application->id]))->status());
            }

            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line('POST comment', $label, $actor, (string) $this->asActor($actor)->postJson(route('division-applications.comments.store', [$division->slug, $application->id]), ['body' => 'Characterization comment'])->status());
            }

            foreach (PermissionWorld::ACTORS as $actor) {
                $fresh   = $this->application($division);
                $lines[] = self::line('DELETE application', $label, $actor, (string) $this->asActor($actor)->deleteJson(route('division-applications.destroy', [$division->slug, $fresh->id]))->status());
            }

            foreach (PermissionWorld::ACTORS as $actor) {
                $comment = $application->comment('Written by admin', $this->world->users['admin']);
                $lines[] = self::line('DELETE others comment', $label, $actor, (string) $this->asActor($actor)->deleteJson(route('division-applications.comments.destroy', [$division->slug, $application->id, $comment->id]))->status());
            }
        }

        $this->assertMatchesSnapshot('DivisionApplicationApi', $lines);
    }

    #[Test]
    public function admin_routes_and_impersonation_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $lines = [];

        $pages = [
            'GET help admin'         => fn () => route('help.admin.home'),
            'GET division turnover'  => fn () => route('reports.division-turnover'),
            'GET impersonate member' => fn () => route('impersonate', $this->world->users['member']),
            'GET impersonate role'   => fn () => route('impersonate-role', 'officer'),
        ];

        foreach ($pages as $label => $url) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line($label, '(status)', $actor, (string) $this->asActor($actor)->get($url())->status());
            }
        }

        $this->assertMatchesSnapshot('AdminRoutesAndImpersonation', $lines);
    }

    #[Test]
    public function member_tag_endpoints_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $tagA   = DivisionTag::create(['name' => 'division_a_public', 'division_id' => $this->world->divisionA->id, 'visibility' => TagVisibility::PUBLIC]);
        $global = DivisionTag::create(['name' => 'global_public', 'division_id' => null, 'visibility' => TagVisibility::PUBLIC]);

        $lines = [];

        foreach (['same_squad' => $this->world->divisionA, 'other_division' => $this->world->divisionB] as $target => $division) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $member  = $this->world->targets[$target];
                $lines[] = self::line('GET member tags', $target, $actor, (string) $this->asActor($actor)->getJson(route('member-tags.get', [$division->slug, $member->clan_id]))->status());
            }

            foreach (PermissionWorld::ACTORS as $actor) {
                $member = $this->world->targets[$target];
                $status = $this->asActor($actor)->postJson(route('member-tags.add', [$division->slug, $member->clan_id]), ['tag_id' => $global->id])->status();
                $member->tags()->detach();
                $lines[] = self::line('POST add global tag', $target, $actor, (string) $status);
            }
        }

        foreach (['division A' => $this->world->divisionA, 'division B' => $this->world->divisionB] as $label => $division) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $name    = substr(md5("{$actor} {$label}"), 0, 12);
                $status  = $this->asActor($actor)->postJson(route('bulk-tags.create-tag', $division->slug), ['name' => $name, 'visibility' => 'public'])->status();
                $landed  = DivisionTag::where('name', $name)->first()?->division?->name ?? 'none';
                $lines[] = self::line('POST create tag', "via {$label} URL", $actor, "{$status} created in {$landed}");
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $targets  = [$this->world->targets['same_squad'], $this->world->targets['other_division']];
            $response = $this->asActor($actor)->postJson(route('bulk-tags.store', $this->world->divisionA->slug), [
                'member_ids' => array_map(fn ($m) => $m->clan_id, $targets),
                'tags'       => [$tagA->id, $global->id],
                'action'     => 'assign',
            ]);

            $tagged = collect($targets)->map(fn ($m) => $m->name . ':' . $m->tags()->pluck('name')->sort()->implode('+'))->implode(', ');

            foreach ($targets as $member) {
                $member->tags()->detach();
            }

            $lines[] = self::line('POST bulk assign', 'same_squad + other_division', $actor, $response->status() . ' ' . $tagged);
        }

        $this->assertMatchesSnapshot('MemberTagEndpoints', $lines);
    }

    private function application(Division $division): DivisionApplication
    {
        return DivisionApplication::factory()->create([
            'division_id' => $division->id,
            'user_id'     => User::factory()->create(['member_id' => null])->id,
            'responses'   => [['label' => 'Timezone', 'value' => 'EST']],
        ]);
    }
}
