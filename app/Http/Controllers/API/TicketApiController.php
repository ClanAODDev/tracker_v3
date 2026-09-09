<?php

namespace App\Http\Controllers\API;

use App\Enums\Rank;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AddTicketCommentRequest;
use App\Http\Requests\Ticket\ReassignTicketRequest;
use App\Http\Requests\Ticket\RejectTicketRequest;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Services\TicketNotificationService;
use App\Support\TicketSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

#[Middleware('auth')]
class TicketApiController extends Controller
{
    public function __construct(
        protected TicketNotificationService $notificationService
    ) {}

    public function index(): JsonResponse
    {
        $tickets = Ticket::where('caller_id', auth()->id())
            ->with(['type', 'owner.member', 'division'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($ticket) => $this->transformTicket($ticket));

        return response()->json(['tickets' => $tickets]);
    }

    public function workableIndex(): JsonResponse
    {
        $user = auth()->user();

        $canWorkAnyType = TicketType::get()->contains(fn ($type) => $type->userCanWork($user));

        if (! $canWorkAnyType) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tickets = Ticket::with(['type', 'owner.member', 'division', 'caller.member'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn ($ticket) => $ticket->type?->userCanWork($user) ?? $user->isRole('admin'))
            ->map(fn ($ticket) => $this->transformTicket($ticket, false, true))
            ->values();

        return response()->json(['tickets' => $tickets]);
    }

    public function own(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        if (! $ticket->canBeOwned()) {
            return response()->json(['error' => "Ticket cannot be assigned from its current state ({$ticket->state})"], 422);
        }

        $ticket->ownTo($user);
        $this->silentNotify(fn () => $this->notificationService->notifyTicketAssigned($ticket, assignee: $user, assignedBy: $user));

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'message' => 'Ticket assigned to you',
            'ticket'  => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function resolve(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        if (! $ticket->canBeResolved()) {
            return response()->json(['error' => "Ticket cannot be resolved from its current state ({$ticket->state})"], 422);
        }

        $ticket->resolve();
        $this->silentNotify(fn () => $this->notificationService->notifyTicketResolved($ticket));

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'message' => 'Ticket resolved',
            'ticket'  => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function reject(RejectTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        if (! $ticket->canBeRejected()) {
            return response()->json(['error' => "Ticket cannot be rejected from its current state ({$ticket->state})"], 422);
        }

        $validated = $request->validated();

        $ticket->reject();
        $this->silentNotify(fn () => $this->notificationService->notifyTicketRejected($ticket, $validated['reason']));

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'message' => 'Ticket rejected',
            'ticket'  => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function reopen(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        if (! $ticket->canBeReopened()) {
            return response()->json(['error' => "Ticket cannot be reopened from its current state ({$ticket->state})"], 422);
        }

        $ticket->reopen();

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'message' => 'Ticket reopened',
            'ticket'  => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function workers(Request $request): JsonResponse
    {
        if (! TicketType::get()->contains(fn ($type) => $type->userCanWork(auth()->user()))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $workers = TicketSerializer::workers(
            $request->filled('ticket_type_id') ? $request->integer('ticket_type_id') : null,
        );

        return response()->json(['workers' => $workers]);
    }

    public function reassign(ReassignTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $assignee = User::with('member')->findOrFail($validated['user_id']);

        $minimumRank = $ticket->type?->minimum_rank?->value ?? Rank::MASTER_SERGEANT->value;

        if (! $assignee->member || $assignee->member->rank->value < $minimumRank) {
            return response()->json(['error' => 'Assignee does not meet the minimum rank for this ticket type'], 422);
        }

        if (! $ticket->canBeOwned()) {
            return response()->json(['error' => "Ticket cannot be reassigned from its current state ({$ticket->state})"], 422);
        }

        $ticket->ownTo($assignee);
        $this->silentNotify(fn () => $this->notificationService->notifyTicketAssigned($ticket, assignee: $assignee, assignedBy: $user));

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'message' => 'Ticket reassigned',
            'ticket'  => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function types(): JsonResponse
    {
        return response()->json(['types' => TicketSerializer::types(auth()->user())]);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $user    = auth()->user();
        $canWork = $ticket->type?->userCanWork($user) ?? $user->isRole('admin');

        if ($ticket->caller_id !== $user->id && ! $canWork) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->load(['type', 'owner.member', 'division', 'caller.member', 'comments.user.member']);

        return response()->json([
            'ticket' => $this->transformTicket($ticket, true, $canWork),
        ]);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $paths = collect($request->file('attachments', []))
            ->map(fn ($file) => $file->store('tickets', 'public'))
            ->values()
            ->all();

        $ticket = Ticket::create([
            'state'          => 'new',
            'ticket_type_id' => $validated['ticket_type_id'],
            'description'    => $validated['description'],
            'caller_id'      => auth()->id(),
            'division_id'    => auth()->user()->member?->division_id ?? 1,
            'attachments'    => $paths ?: null,
        ]);

        $this->silentNotify(fn () => $this->notificationService->notifyTicketCreated($ticket), $ticket->id);

        $ticket->load(['type', 'owner', 'division']);

        return response()->json([
            'ticket'  => $this->transformTicket($ticket),
            'message' => 'Ticket created successfully',
        ], 201);
    }

    public function addComment(AddTicketCommentRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if ($ticket->caller_id !== $user->id && ! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $comment = $ticket->comments()->create([
            'body'    => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $comment->load('user.member');

        $this->notificationService->notifyCommentAdded($ticket, $comment);

        return response()->json([
            'comment' => [
                'id'   => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id'       => $comment->user->id,
                    'name'     => $comment->user->name,
                    'avatar'   => $comment->user->member?->getDiscordAvatarUrl(),
                    'is_admin' => $comment->user->isRole('admin'),
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
            'message' => 'Comment added successfully',
        ], 201);
    }

    protected function silentNotify(callable $fn, ?int $ticketId = null): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error('Failed to send ticket notification', array_filter([
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]));
        }
    }

    protected function transformTicket(Ticket $ticket, bool $includeComments = false, bool $includeCaller = false): array
    {
        return TicketSerializer::ticket($ticket, $includeComments, $includeCaller);
    }
}
