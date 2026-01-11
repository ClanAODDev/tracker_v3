<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\TicketNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketApiController extends Controller
{
    public function __construct(
        protected TicketNotificationService $notificationService
    ) {
        $this->middleware('auth');
    }

    public function index(): JsonResponse
    {
        $tickets = Ticket::where('caller_id', auth()->id())
            ->with(['type', 'owner', 'division'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($ticket) => $this->transformTicket($ticket));

        return response()->json(['tickets' => $tickets]);
    }

    public function adminIndex(): JsonResponse
    {
        $user = auth()->user();

        $canWorkAnyType = TicketType::get()->contains(fn ($type) => $type->userCanWork($user));

        if (! $canWorkAnyType) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tickets = Ticket::with(['type', 'owner', 'division', 'caller'])
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

        $ticket->ownTo($user);
        $this->notificationService->notifyTicketAssigned($ticket, auth()->user());

        $ticket->load(['type', 'owner', 'division', 'caller', 'comments.user']);

        return response()->json([
            'message' => 'Ticket assigned to you',
            'ticket' => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function resolve(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        $ticket->resolve();
        $this->notificationService->notifyTicketResolved($ticket);

        $ticket->load(['type', 'owner', 'division', 'caller', 'comments.user']);

        return response()->json([
            'message' => 'Ticket resolved',
            'ticket' => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function reject(Request $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $ticket->reject();
        $this->notificationService->notifyTicketRejected($ticket, $validated['reason']);

        $ticket->load(['type', 'owner', 'division', 'caller', 'comments.user']);

        return response()->json([
            'message' => 'Ticket rejected',
            'ticket' => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function reopen(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if (! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'You do not have permission to work this ticket type'], 403);
        }

        $ticket->reopen();

        $ticket->load(['type', 'owner', 'division', 'caller', 'comments.user']);

        return response()->json([
            'message' => 'Ticket reopened',
            'ticket' => $this->transformTicket($ticket, true, true),
        ]);
    }

    public function types(): JsonResponse
    {
        $user = auth()->user();
        $userRoleId = $user->role->value ? (string) $user->role->value : null;

        $types = TicketType::orderBy('display_order')
            ->get()
            ->filter(function ($type) use ($userRoleId) {
                $roleAccess = $type->role_access ?? [];
                if (empty($roleAccess)) {
                    return true;
                }

                return $userRoleId && in_array($userRoleId, $roleAccess);
            })
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'description' => $type->description,
                'boilerplate' => $type->boilerplate,
            ])
            ->values();

        return response()->json(['types' => $types]);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $user = auth()->user();
        $canWork = $ticket->type?->userCanWork($user) ?? $user->isRole('admin');

        if ($ticket->caller_id !== $user->id && ! $canWork) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->load(['type', 'owner', 'division', 'caller', 'comments.user']);

        return response()->json([
            'ticket' => $this->transformTicket($ticket, true, $canWork),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'description' => 'required|string|min:25',
        ]);

        $ticket = Ticket::create([
            'state' => 'new',
            'ticket_type_id' => $validated['ticket_type_id'],
            'description' => $validated['description'],
            'caller_id' => auth()->id(),
            'division_id' => auth()->user()->member?->division_id ?? 1,
        ]);

        $this->notificationService->notifyTicketCreated($ticket);

        $ticket->load(['type', 'owner', 'division']);

        return response()->json([
            'ticket' => $this->transformTicket($ticket),
            'message' => 'Ticket created successfully',
        ], 201);
    }

    public function addComment(Request $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();

        if ($ticket->caller_id !== $user->id && ! ($ticket->type?->userCanWork($user) ?? $user->isRole('admin'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:5',
        ]);

        $comment = $ticket->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $comment->load('user');

        $this->notificationService->notifyCommentAdded($ticket, $comment);

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'is_admin' => $comment->user->isRole('admin'),
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
            'message' => 'Comment added successfully',
        ], 201);
    }

    protected function transformTicket(Ticket $ticket, bool $includeComments = false, bool $includeCaller = false): array
    {
        $type = $ticket->type;
        $division = $ticket->division;
        $owner = $ticket->owner;
        $caller = $ticket->caller;

        $data = [
            'id' => $ticket->id,
            'state' => $ticket->state,
            'state_color' => $ticket->stateColors[$ticket->state] ?? 'gray',
            'description' => $ticket->description,
            'type' => $type ? [
                'id' => $type->id,
                'name' => $type->name,
            ] : null,
            'division' => $division ? [
                'id' => $division->id,
                'name' => $division->name,
            ] : null,
            'owner' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->name,
            ] : null,
            'created_at' => $ticket->created_at->toIso8601String(),
            'updated_at' => $ticket->updated_at->toIso8601String(),
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
        ];

        if ($includeCaller) {
            $data['caller'] = $caller ? [
                'id' => $caller->id,
                'name' => $caller->name,
            ] : null;
        }

        if ($includeComments) {
            $data['comments'] = $ticket->comments->map(fn ($comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'is_admin' => $comment->user->isRole('admin'),
                ] : null,
                'created_at' => $comment->created_at->toIso8601String(),
            ]);
        }

        return $data;
    }
}
