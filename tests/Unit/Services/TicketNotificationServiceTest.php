<?php

namespace Tests\Unit\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketType;
use App\Notifications\Channel\NotifyAdminTicketCreated;
use App\Notifications\DM\NotifyCallerTicketUpdated;
use App\Notifications\DM\NotifyNewTicketOwner;
use App\Notifications\DM\NotifyUserTicketCreated;
use App\Notifications\React\TicketReaction;
use App\Services\TicketNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TicketNotificationServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private TicketNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketNotificationService;
        Notification::fake();
    }

    public function test_notify_ticket_created_sends_user_notification()
    {
        $user = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketCreated($ticket);

        Notification::assertSentTo($ticket, NotifyUserTicketCreated::class);
    }

    public function test_notify_ticket_created_sends_admin_notification()
    {
        $user = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketCreated($ticket);

        Notification::assertSentTo($ticket, NotifyAdminTicketCreated::class);
    }

    public function test_notify_ticket_created_auto_assigns_when_ticket_type_has_auto_assign()
    {
        $user = $this->createMemberWithUser();
        $assignee = $this->createAdmin();
        $ticketType = TicketType::factory()->create([
            'auto_assign_to_id' => $assignee->id,
        ]);

        $this->actingAs($user);

        $ticket = Ticket::factory()->create([
            'caller_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'state' => 'new',
        ]);

        $this->service->notifyTicketCreated($ticket);

        $ticket->refresh();
        $this->assertEquals($assignee->id, $ticket->owner_id);
        $this->assertEquals('assigned', $ticket->state);
    }

    public function test_notify_ticket_assigned_sends_caller_notification()
    {
        $caller = $this->createMemberWithUser();
        $assignee = $this->createAdmin();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->actingAs($assignee);

        $this->service->notifyTicketAssigned($ticket, $assignee);

        Notification::assertSentTo($ticket, NotifyCallerTicketUpdated::class);
    }

    public function test_notify_ticket_assigned_sends_owner_notification()
    {
        $caller = $this->createMemberWithUser();
        $assignee = $this->createAdmin();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->actingAs($assignee);

        $this->service->notifyTicketAssigned($ticket, $assignee);

        Notification::assertSentTo($ticket, NotifyNewTicketOwner::class);
    }

    public function test_notify_ticket_assigned_sends_reaction()
    {
        $caller = $this->createMemberWithUser();
        $assignee = $this->createAdmin();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->actingAs($assignee);

        $this->service->notifyTicketAssigned($ticket, $assignee);

        Notification::assertSentTo($ticket, TicketReaction::class);
    }

    public function test_notify_ticket_resolved_sends_reaction()
    {
        $caller = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketResolved($ticket);

        Notification::assertSentTo($ticket, TicketReaction::class);
    }

    public function test_notify_ticket_resolved_notifies_caller()
    {
        $caller = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketResolved($ticket);

        Notification::assertSentTo($caller, NotifyCallerTicketUpdated::class);
    }

    public function test_notify_ticket_rejected_sends_reaction()
    {
        $caller = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketRejected($ticket, 'Not enough information');

        Notification::assertSentTo($ticket, TicketReaction::class);
    }

    public function test_notify_ticket_rejected_notifies_caller_with_reason()
    {
        $caller = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $this->service->notifyTicketRejected($ticket, 'Duplicate request');

        Notification::assertSentTo($caller, NotifyCallerTicketUpdated::class);
    }

    public function test_notify_comment_added_does_not_notify_when_commenter_is_caller()
    {
        $caller = $this->createMemberWithUser();
        $ticketType = TicketType::factory()->create();
        $ticket = Ticket::factory()->create([
            'caller_id' => $caller->id,
            'ticket_type_id' => $ticketType->id,
        ]);

        $comment = TicketComment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $caller->id,
            'body' => 'Self comment',
        ]);

        $this->service->notifyCommentAdded($ticket, $comment);

        Notification::assertNotSentTo($caller, NotifyCallerTicketUpdated::class);
    }
}
