<?php

namespace Tests\Feature\Controllers;

use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TicketPageTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[Test]
    public function index_renders_the_users_tickets()
    {
        $officer = $this->createOfficer();
        $type    = TicketType::factory()->create();
        $ticket  = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $officer->member->division_id,
            'ticket_type_id' => $type->id,
        ]);

        $this->actingAs($officer)
            ->get(route('help.tickets.widget'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('tickets/index')
                ->where('view', 'user')
                ->where('tickets', fn ($tickets) => collect($tickets)->contains('id', $ticket->id))
                ->has('ticketTypes'));
    }

    #[Test]
    public function show_renders_ticket_detail_for_the_caller()
    {
        $officer = $this->createOfficer();
        $type    = TicketType::factory()->create();
        $ticket  = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $officer->member->division_id,
            'ticket_type_id' => $type->id,
        ]);

        $this->actingAs($officer)
            ->get(route('help.tickets.show', $ticket))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('tickets/show')
                ->where('ticket.id', $ticket->id)
                ->has('ticket.comments'));
    }

    #[Test]
    public function show_forbids_an_unrelated_user()
    {
        $type   = TicketType::factory()->create();
        $ticket = Ticket::factory()->create(['ticket_type_id' => $type->id]);
        $viewer = $this->createMemberWithUser();

        $this->actingAs($viewer)
            ->get(route('help.tickets.show', $ticket))
            ->assertForbidden();
    }

    #[Test]
    public function create_renders_the_type_picker()
    {
        $officer = $this->createOfficer();
        TicketType::factory()->create();

        $this->actingAs($officer)
            ->get(route('help.tickets.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('tickets/create')
                ->has('ticketTypes'));
    }
}
