<?php

namespace Tests\Unit\Models;

use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class TicketTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function scope_new_returns_only_new_tickets()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $newTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $assignedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'assigned',
        ]);

        $results = Ticket::new()->get();

        $this->assertTrue($results->contains($newTicket));
        $this->assertFalse($results->contains($assignedTicket));
    }

    #[Test]
    public function scope_assigned_returns_only_assigned_tickets()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $newTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $assignedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'assigned',
        ]);

        $results = Ticket::assigned()->get();

        $this->assertFalse($results->contains($newTicket));
        $this->assertTrue($results->contains($assignedTicket));
    }

    #[Test]
    public function scope_resolved_returns_only_resolved_tickets()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $newTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $resolvedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'resolved',
        ]);

        $results = Ticket::resolved()->get();

        $this->assertFalse($results->contains($newTicket));
        $this->assertTrue($results->contains($resolvedTicket));
    }

    #[Test]
    public function state_color_attribute_returns_correct_color()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $newTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $assignedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'assigned',
        ]);

        $this->assertEquals('info', $newTicket->stateColor);
        $this->assertEquals('accent', $assignedTicket->stateColor);
    }

    #[Test]
    public function own_to_assigns_owner_and_changes_state()
    {
        $caller     = $this->createMemberWithUser();
        $owner      = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $this->actingAs($owner);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $caller->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $ticket->ownTo($owner);
        $ticket->refresh();

        $this->assertEquals($owner->id, $ticket->owner_id);
        $this->assertEquals('assigned', $ticket->state);
    }

    #[Test]
    public function resolve_sets_state_and_timestamps()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $this->actingAs($user);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'assigned',
        ]);

        $ticket->resolve();
        $ticket->refresh();

        $this->assertEquals('resolved', $ticket->state);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertEquals($user->id, $ticket->owner_id);
    }

    #[Test]
    public function reopen_clears_resolved_at_and_sets_state()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $this->actingAs($user);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'resolved',
            'resolved_at'    => now(),
        ]);

        $ticket->reopen();
        $ticket->refresh();

        $this->assertEquals('assigned', $ticket->state);
        $this->assertNull($ticket->resolved_at);
    }

    #[Test]
    public function reject_sets_state_and_timestamps()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $this->actingAs($user);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state'          => 'new',
        ]);

        $ticket->reject();
        $ticket->refresh();

        $this->assertEquals('rejected', $ticket->state);
        $this->assertNotNull($ticket->resolved_at);
    }

    #[Test]
    public function is_resolved_returns_correct_value()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $unresolvedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'resolved_at'    => null,
        ]);

        $resolvedTicket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
            'resolved_at'    => now(),
        ]);

        $this->assertFalse((bool) $unresolvedTicket->isResolved());
        $this->assertTrue((bool) $resolvedTicket->isResolved());
    }

    #[Test]
    public function say_creates_comment()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $this->actingAs($user);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $ticket->say('Test comment');

        $this->assertCount(1, $ticket->comments);
        $this->assertEquals('Test comment', $ticket->comments->first()->body);
    }

    #[Test]
    public function has_external_message_id_returns_correct_value()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $ticketWithId = Ticket::factory()->create([
            'caller_id'           => $user->id,
            'ticket_type_id'      => $ticketType->id,
            'external_message_id' => '123456',
        ]);

        $ticketWithoutId = Ticket::factory()->create([
            'caller_id'           => $user->id,
            'ticket_type_id'      => $ticketType->id,
            'external_message_id' => '',
        ]);

        $this->assertTrue($ticketWithId->hasExternalMessageId());
        $this->assertFalse($ticketWithoutId->hasExternalMessageId());
    }

    #[Test]
    public function type_relationship_returns_correct_ticket_type()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create(['name' => 'Test Type']);

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->assertEquals('Test Type', $ticket->type->name);
    }

    #[Test]
    public function caller_relationship_returns_correct_user()
    {
        $user       = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();

        $ticket = Ticket::factory()->create([
            'caller_id'      => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->assertTrue($ticket->caller->is($user));
    }
}
