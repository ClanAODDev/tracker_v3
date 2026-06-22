<?php

namespace Tests\Feature\API;

use App\Enums\Rank;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TicketApiTest extends TestCase
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
    public function index_requires_authentication()
    {
        $response = $this->getJson('/api/tickets');

        $response->assertUnauthorized();
    }

    #[Test]
    public function index_returns_user_tickets()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $userTicket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser   = $this->createMemberWithUser(['division_id' => $division->id]);
        $otherTicket = Ticket::factory()->create([
            'caller_id'      => $otherUser->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($officer)
            ->getJson('/api/tickets');

        $response->assertOk();
        $response->assertJsonPath('tickets.0.id', $userTicket->id);
        $response->assertJsonMissing(['id' => $otherTicket->id]);
    }

    #[Test]
    public function types_returns_available_ticket_types()
    {
        $officer    = $this->createOfficer();
        $ticketType = TicketType::factory()->create([
            'name' => 'General Support',
        ]);

        $response = $this->actingAs($officer)
            ->getJson('/api/tickets/types');

        $response->assertOk();
        $response->assertJsonStructure([
            'types' => [
                '*' => ['id', 'name', 'slug', 'description', 'boilerplate'],
            ],
        ]);
    }

    #[Test]
    public function store_creates_ticket()
    {
        $officer    = $this->createOfficer();
        $ticketType = TicketType::factory()->create();

        $response = $this->actingAs($officer)
            ->postJson('/api/tickets', [
                'ticket_type_id' => $ticketType->id,
                'description'    => 'This is a test ticket with sufficient description length.',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Ticket created successfully');
        $this->assertDatabaseHas('tickets', [
            'caller_id'      => $officer->id,
            'ticket_type_id' => $ticketType->id,
        ]);
    }

    #[Test]
    public function store_validates_minimum_description_length()
    {
        $officer    = $this->createOfficer();
        $ticketType = TicketType::factory()->create();

        $response = $this->actingAs($officer)
            ->postJson('/api/tickets', [
                'ticket_type_id' => $ticketType->id,
                'description'    => 'Too short',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');
    }

    #[Test]
    public function show_returns_ticket_for_owner()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($officer)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
        $response->assertJsonPath('ticket.id', $ticket->id);
    }

    #[Test]
    public function show_returns_403_for_non_owner()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_any_ticket()
    {
        $admin      = $this->createAdmin();
        $division   = $admin->member->division;
        $ticketType = TicketType::factory()->create();

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);
        $ticket    = Ticket::factory()->create([
            'caller_id'      => $otherUser->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
    }

    #[Test]
    public function add_comment_to_ticket()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($officer)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'This is a comment on the ticket.',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Comment added successfully');
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id'   => $officer->id,
        ]);
    }

    #[Test]
    public function cannot_add_comment_to_others_ticket()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Trying to comment on someone elses ticket.',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function workers_requires_authentication()
    {
        $this->getJson('/api/tickets/workers')->assertUnauthorized();
    }

    #[Test]
    public function workers_returns_403_for_unauthorized_user()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)->getJson('/api/tickets/workers')->assertForbidden();
    }

    #[Test]
    public function workers_returns_members_meeting_default_minimum_rank()
    {
        $admin         = $this->createAdmin();
        $eligibleUser  = $this->createMemberWithUser(['rank' => Rank::MASTER_SERGEANT]);
        $ineligibleUser = $this->createMemberWithUser(['rank' => Rank::SERGEANT]);

        $response = $this->actingAs($admin)->getJson('/api/tickets/workers');

        $response->assertOk();
        $workerIds = collect($response->json('workers'))->pluck('id');
        $this->assertTrue($workerIds->contains($eligibleUser->id));
        $this->assertFalse($workerIds->contains($ineligibleUser->id));
    }

    #[Test]
    public function workers_filters_by_ticket_type_minimum_rank()
    {
        $admin      = $this->createAdmin();
        $ticketType = TicketType::factory()->requiresMinimumRank(Rank::STAFF_SERGEANT)->create();

        $eligibleUser   = $this->createMemberWithUser(['rank' => Rank::STAFF_SERGEANT]);
        $ineligibleUser = $this->createMemberWithUser(['rank' => Rank::SERGEANT]);

        $response = $this->actingAs($admin)
            ->getJson("/api/tickets/workers?ticket_type_id={$ticketType->id}");

        $response->assertOk();
        $workerIds = collect($response->json('workers'))->pluck('id');
        $this->assertTrue($workerIds->contains($eligibleUser->id));
        $this->assertFalse($workerIds->contains($ineligibleUser->id));
    }

    #[Test]
    public function workers_falls_back_to_default_rank_when_type_has_no_minimum_rank()
    {
        $admin      = $this->createAdmin();
        $ticketType = TicketType::factory()->create(['minimum_rank' => null]);

        $eligibleUser   = $this->createMemberWithUser(['rank' => Rank::MASTER_SERGEANT]);
        $ineligibleUser = $this->createMemberWithUser(['rank' => Rank::SERGEANT]);

        $response = $this->actingAs($admin)
            ->getJson("/api/tickets/workers?ticket_type_id={$ticketType->id}");

        $response->assertOk();
        $workerIds = collect($response->json('workers'))->pluck('id');
        $this->assertTrue($workerIds->contains($eligibleUser->id));
        $this->assertFalse($workerIds->contains($ineligibleUser->id));
    }

    #[Test]
    public function deleting_ticket_type_deletes_associated_tickets()
    {
        $officer    = $this->createOfficer();
        $division   = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $officer->id,
            'division_id'    => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $ticketId = $ticket->id;

        $ticketType->delete();

        $this->assertNull(Ticket::find($ticketId));
    }
}
