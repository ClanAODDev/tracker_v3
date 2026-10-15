<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;

class TicketApiMatrixTest extends PermissionMatrixTestCase
{
    private TicketType $sergeantType;

    private TicketType $adminOnlyType;

    #[Test]
    public function ticket_api_matches_the_recorded_matrix(): void
    {
        Http::fake();
        Notification::fake();
        Queue::fake();

        $this->buildWorld();

        $this->sergeantType  = TicketType::factory()->create(['name' => 'Sergeant-level help', 'minimum_rank' => Rank::SERGEANT]);
        $this->adminOnlyType = TicketType::factory()->create(['name' => 'Admin-only help', 'minimum_rank' => null]);

        $lines = [];

        $collections = [
            'GET index'    => fn () => route('tickets.index'),
            'GET types'    => fn () => route('tickets.types'),
            'GET workable' => fn () => route('tickets.workable'),
            'GET workers'  => fn () => route('tickets.workers'),
        ];

        foreach ($collections as $label => $url) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line($label, '-', $actor, (string) $this->asActor($actor)->getJson($url())->status());
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $status = $this->asActor($actor)->postJson(route('tickets.store'), [
                'ticket_type_id' => $this->sergeantType->id,
                'description'    => 'A characterization ticket with enough text.',
            ])->status();
            $lines[] = self::line('POST store', '-', $actor, (string) $status);
        }

        $variants = [
            'own ticket'                   => fn (string $actor) => $this->ticket($this->world->users[$actor]->id, $this->sergeantType),
            'others sergeant-level ticket' => fn () => $this->ticket($this->world->users['admin']->id, $this->sergeantType),
            'others admin-only ticket'     => fn () => $this->ticket($this->world->users['admin']->id, $this->adminOnlyType),
        ];

        $actions = [
            'GET show'      => fn (Ticket $t) => ['get', route('tickets.show', $t), []],
            'POST comment'  => fn (Ticket $t) => ['post', route('tickets.comments.store', $t), ['body' => 'A characterization comment.']],
            'POST own'      => fn (Ticket $t) => ['post', route('tickets.own', $t), []],
            'POST resolve'  => fn (Ticket $t) => ['post', route('tickets.resolve', $t), []],
            'POST reject'   => fn (Ticket $t) => ['post', route('tickets.reject', $t), ['reason' => 'Characterization reason.']],
            'POST reopen'   => fn (Ticket $t) => ['post', route('tickets.reopen', $t), []],
            'POST reassign' => fn (Ticket $t) => ['post', route('tickets.reassign', $t), ['user_id' => $this->world->users['senior_leader']->id]],
        ];

        foreach ($actions as $action => $request) {
            foreach ($variants as $variant => $makeTicket) {
                foreach (PermissionWorld::ACTORS as $actor) {
                    [$method, $url, $data] = $request($makeTicket($actor));

                    $response = $method === 'get'
                        ? $this->asActor($actor)->getJson($url)
                        : $this->asActor($actor)->postJson($url, $data);

                    $lines[] = self::line($action, $variant, $actor, (string) $response->status());
                }
            }
        }

        $this->assertMatchesSnapshot('TicketApi', $lines);
    }

    private function ticket(int $callerId, TicketType $type): Ticket
    {
        return Ticket::factory()->create([
            'caller_id'      => $callerId,
            'ticket_type_id' => $type->id,
            'division_id'    => $this->world->divisionA->id,
        ]);
    }
}
