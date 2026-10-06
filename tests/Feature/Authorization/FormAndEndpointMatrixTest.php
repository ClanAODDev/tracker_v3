<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Filament\Admin\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Admin\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\CreateDivisionTag;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\CreateMemberAward;
use App\Filament\Mod\Resources\RankActionResource\Pages\CreateRankAction;
use App\Models\Award;
use App\Models\DivisionTag;
use App\Models\RankAction;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketType;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class FormAndEndpointMatrixTest extends PermissionMatrixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Notification::fake();
        Queue::fake();
    }

    #[Test]
    public function member_award_form_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $awards = [
            'clan-wide award'  => Award::factory()->create(['name' => 'clan-wide award', 'division_id' => null]),
            'division A award' => Award::factory()->create(['name' => 'division A award', 'division_id' => $this->world->divisionA->id]),
            'division B award' => Award::factory()->create(['name' => 'division B award', 'division_id' => $this->world->divisionB->id]),
        ];

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);

            $lines[] = self::line('award form division_filter', '(options / default)', $actor, $this->safely(function () {
                $page    = Livewire::test(CreateMemberAward::class);
                $options = [];
                $page->assertFormFieldExists('division_filter', 'form', function ($field) use (&$options) {
                    $options = array_values(array_map(fn ($label) => (string) $label, $field->getOptions()));

                    return true;
                });
                $default = $page->get('data.division_filter');

                return '[' . implode(', ', $options) . '] default=' . ($default === null ? 'none' : ($default === 'clan-wide' ? 'clan-wide' : $this->divisionName($default)));
            }));

            foreach ($awards as $label => $award) {
                $lines[] = self::line('award form award_id rule', $label, $actor, $this->safely(function () use ($award) {
                    $page = Livewire::test(CreateMemberAward::class)
                        ->fillForm(['award_id' => $award->id])
                        ->call('create');

                    return $page->errors()->has('data.award_id') ? 'rejected: ' . implode(' | ', $page->errors()->get('data.award_id')) : 'accepted';
                }));
            }
        }

        $this->assertMatchesSnapshot('MemberAwardForm', $lines);
    }

    #[Test]
    public function rank_action_form_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $target = $this->world->targets['same_squad'];
        RankAction::create([
            'member_id'    => $target->id,
            'requester_id' => $this->world->targets['higher_rank']->id,
            'rank'         => Rank::SPECIALIST,
        ]);

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);

            $lines[] = self::line('rank action form', 'override checkbox offered', $actor, $this->safely(function () {
                try {
                    Livewire::test(CreateRankAction::class)->assertFormFieldExists('override_existing');

                    return 'yes';
                } catch (AssertionFailedError) {
                    return 'no';
                }
            }));

            $lines[] = self::line('rank action form', 'action options for same_squad', $actor, $this->safely(function () use ($target) {
                $page    = Livewire::test(CreateRankAction::class)->fillForm(['member_id' => $target->id]);
                $options = [];
                $page->assertFormFieldExists('action', 'form', function ($field) use (&$options) {
                    $options = array_keys($field->getOptions());

                    return true;
                });

                return '[' . implode(', ', $options) . ']';
            }));

            $lines[] = self::line('rank action form', '30-day rule with override checked', $actor, $this->safely(function () use ($target) {
                $page = Livewire::test(CreateRankAction::class)
                    ->fillForm(['member_id' => $target->id, 'override_existing' => true])
                    ->call('create');

                return collect($page->errors()->get('data.member_id'))->contains(fn ($error) => str_contains($error, 'already exists')) ? 'blocked by 30-day rule' : 'not blocked';
            }));
        }

        $this->assertMatchesSnapshot('RankActionForm', $lines);
    }

    #[Test]
    public function tag_visibility_ticket_pages_preview_and_tag_creation_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $lines = [];

        $tags = [
            'public tag'         => TagVisibility::PUBLIC,
            'officers tag'       => TagVisibility::OFFICERS,
            'senior leaders tag' => TagVisibility::SENIOR_LEADERS,
        ];

        foreach ($tags as $label => $visibility) {
            $tag = DivisionTag::create(['name' => $label, 'division_id' => $this->world->divisionA->id, 'visibility' => $visibility]);

            foreach (PermissionWorld::ACTORS as $actor) {
                $this->actAs($actor);
                $lines[] = self::line('DivisionTag::isVisibleTo', $label, $actor, $this->outcome(fn () => $tag->isVisibleTo($this->world->users[$actor])));
            }
        }

        $sergeantType  = TicketType::factory()->create(['name' => 'Sergeant-level help', 'minimum_rank' => Rank::SERGEANT]);
        $adminOnlyType = TicketType::factory()->create(['name' => 'Admin-only help', 'minimum_rank' => null]);
        $tickets       = [
            'others sergeant-level ticket' => $this->ticket($this->world->users['admin']->id, $sergeantType),
            'others admin-only ticket'     => $this->ticket($this->world->users['admin']->id, $adminOnlyType),
        ];

        foreach (PermissionWorld::ACTORS as $actor) {
            $response = $this->asActor($actor)->get(route('help.tickets.widget', ['view' => 'all']));
            $listed   = $response->status() === 200
                ? collect($response->viewData('page')['props']['tickets'])->pluck('id')->map(fn ($id) => array_search($id, array_map(fn ($t) => $t->id, $tickets), true))->sort()->values()->implode(', ')
                : '';
            $lines[] = self::line('GET tickets ?view=all', '(listed tickets)', $actor, $response->status() . " [{$listed}]");
        }

        foreach ($tickets as $label => $ticket) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line('GET ticket page', $label, $actor, (string) $this->asActor($actor)->get(route('help.tickets.show', $ticket))->status());
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $response = $this->asActor($actor)->get(route('auth.discord.pending', ['preview' => $this->world->divisionA->slug]));
            $target   = $response->isRedirect() ? ' -> ' . (parse_url($response->headers->get('Location'), PHP_URL_PATH) ?: '/') : '';
            $lines[]  = self::line('GET discord pending preview', '(status)', $actor, $response->status() . $target);
        }

        foreach (['same_squad' => $this->world->divisionA, 'other_division' => $this->world->divisionB] as $target => $division) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $name    = substr(md5("{$actor} {$target}"), 0, 12);
                $member  = $this->world->targets[$target];
                $status  = $this->asActor($actor)->postJson(route('member-tags.create', [$division->slug, $member->clan_id]), ['name' => $name, 'visibility' => 'public'])->status();
                $landed  = DivisionTag::where('name', $name)->first()?->division?->name ?? 'none';
                $lines[] = self::line('POST create tag for member', "{$target} via {$division->name} URL", $actor, "{$status} created in {$landed}");
            }
        }

        $this->assertMatchesSnapshot('TagTicketPreviewEndpoints', $lines);
    }

    #[Test]
    public function division_tag_form_visibility_options_match_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);

            $lines[] = self::line('tag form visibility options', '-', $actor, $this->safely(function () {
                $options = [];
                Livewire::test(CreateDivisionTag::class)->assertFormFieldExists('visibility', 'form', function ($field) use (&$options) {
                    $options = array_keys($field->getOptions());

                    return true;
                });

                return '[' . implode(', ', $options) . ']';
            }));
        }

        $this->assertMatchesSnapshot('DivisionTagForm', $lines);
    }

    #[Test]
    public function admin_ticket_comment_author_badge_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('admin');
        $this->actAs('admin');

        $ticket = $this->ticket($this->world->users['member']->id, TicketType::factory()->create());
        $lines  = [];

        foreach (['admin', 'officer', 'admin_viewing_as_officer'] as $author) {
            $comment = TicketComment::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $this->world->users[$author]->id]);

            $lines[] = self::line('ticket comment author badge', "comment by {$author}", 'admin', $this->safely(function () use ($ticket, $comment) {
                $column = Livewire::test(CommentsRelationManager::class, ['ownerRecord' => $ticket, 'pageClass' => EditTicket::class])
                    ->instance()->getTable()->getColumn('user.name');
                $column->record($comment);

                return (string) $column->getColor($column->getState());
            }));
        }

        $this->assertMatchesSnapshot('AdminTicketCommentBadge', $lines);
    }

    private function ticket(int $callerId, TicketType $type): Ticket
    {
        return Ticket::factory()->create(['caller_id' => $callerId, 'ticket_type_id' => $type->id, 'division_id' => $this->world->divisionA->id]);
    }

    private function divisionName($id): string
    {
        return $id == $this->world->divisionA->id ? 'Division A' : ($id == $this->world->divisionB->id ? 'Division B' : "#{$id}");
    }

    private function safely(callable $resolve): string
    {
        try {
            return (string) $resolve();
        } catch (Throwable $e) {
            return str_contains($e->getMessage(), 'on null') ? 'page not accessible' : 'error:' . class_basename($e);
        }
    }
}
