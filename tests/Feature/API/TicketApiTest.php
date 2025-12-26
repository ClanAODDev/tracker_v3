<?php

namespace Tests\Feature\API;

use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/tickets');

        $response->assertUnauthorized();
    }

    public function test_index_returns_user_tickets()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $userTicket = Ticket::factory()->create([
            'caller_id' => $officer->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);
        $otherTicket = Ticket::factory()->create([
            'caller_id' => $otherUser->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($officer)
            ->getJson('/api/tickets');

        $response->assertOk();
        $response->assertJsonPath('tickets.0.id', $userTicket->id);
        $response->assertJsonMissing(['id' => $otherTicket->id]);
    }

    public function test_types_returns_available_ticket_types()
    {
        $officer = $this->createOfficer();
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

    public function test_store_creates_ticket()
    {
        $officer = $this->createOfficer();
        $ticketType = TicketType::factory()->create();

        $response = $this->actingAs($officer)
            ->postJson('/api/tickets', [
                'ticket_type_id' => $ticketType->id,
                'description' => 'This is a test ticket with sufficient description length.',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Ticket created successfully');
        $this->assertDatabaseHas('tickets', [
            'caller_id' => $officer->id,
            'ticket_type_id' => $ticketType->id,
        ]);
    }

    public function test_store_validates_minimum_description_length()
    {
        $officer = $this->createOfficer();
        $ticketType = TicketType::factory()->create();

        $response = $this->actingAs($officer)
            ->postJson('/api/tickets', [
                'ticket_type_id' => $ticketType->id,
                'description' => 'Too short',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');
    }

    public function test_show_returns_ticket_for_owner()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id' => $officer->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($officer)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
        $response->assertJsonPath('ticket.id', $ticket->id);
    }

    public function test_show_returns_403_for_non_owner()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id' => $officer->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_view_any_ticket()
    {
        $admin = $this->createAdmin();
        $division = $admin->member->division;
        $ticketType = TicketType::factory()->create();

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);
        $ticket = Ticket::factory()->create([
            'caller_id' => $otherUser->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
    }

    public function test_add_comment_to_ticket()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id' => $officer->id,
            'division_id' => $division->id,
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
            'user_id' => $officer->id,
        ]);
    }

    public function test_cannot_add_comment_to_others_ticket()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id' => $officer->id,
            'division_id' => $division->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $otherUser = $this->createMemberWithUser(['division_id' => $division->id]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Trying to comment on someone elses ticket.',
            ]);

        $response->assertForbidden();
    }
}
