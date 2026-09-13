<?php

namespace App\Support;

use App\Enums\Rank;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TicketSerializer
{
    public static function ticket(Ticket $ticket, bool $includeComments = false, bool $includeCaller = false): array
    {
        $type     = $ticket->type;
        $division = $ticket->division;
        $owner    = $ticket->owner;
        $caller   = $ticket->caller;

        $data = [
            'id'          => $ticket->id,
            'state'       => $ticket->state,
            'state_color' => $ticket->stateColors[$ticket->state] ?? 'gray',
            'description' => $ticket->description,
            'type'        => $type ? ['id' => $type->id, 'name' => $type->name] : null,
            'division'    => $division ? ['id' => $division->id, 'name' => $division->name] : null,
            'owner'       => $owner ? [
                'id'     => $owner->id,
                'name'   => $owner->name,
                'avatar' => $owner->member?->getDiscordAvatarUrl(),
            ] : null,
            'created_at'  => $ticket->created_at->toIso8601String(),
            'updated_at'  => $ticket->updated_at->toIso8601String(),
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
            'attachments' => collect($ticket->attachments ?? [])
                ->map(fn ($path) => Storage::disk('public')->url($path))
                ->all(),
        ];

        if ($includeCaller) {
            $data['caller'] = $caller ? [
                'id'     => $caller->id,
                'name'   => $caller->name,
                'avatar' => $caller->member?->getDiscordAvatarUrl(),
            ] : null;
        }

        if ($includeComments) {
            $data['comments'] = $ticket->comments->map(fn ($comment) => [
                'id'   => $comment->id,
                'body' => $comment->body,
                'user' => $comment->user ? [
                    'id'       => $comment->user->id,
                    'name'     => $comment->user->name,
                    'avatar'   => $comment->user->member?->getDiscordAvatarUrl(),
                    'is_admin' => $comment->user->isRole('admin'),
                ] : null,
                'created_at' => $comment->created_at->toIso8601String(),
            ])->values();
        }

        return $data;
    }

    public static function types(User $user): Collection
    {
        $userRoleId = $user->role->value ? (string) $user->role->value : null;

        return TicketType::orderBy('display_order')
            ->get()
            ->filter(function ($type) use ($userRoleId) {
                $roleAccess = $type->role_access ?? [];

                return empty($roleAccess) || ($userRoleId && in_array($userRoleId, $roleAccess));
            })
            ->map(fn ($type) => [
                'id'          => $type->id,
                'name'        => $type->name,
                'slug'        => $type->slug,
                'description' => $type->description,
                'boilerplate' => $type->boilerplate,
            ])
            ->values();
    }

    public static function workers(?int $ticketTypeId = null, ?int $excludeUserId = null): Collection
    {
        $minimumRank = Rank::MASTER_SERGEANT->value;

        if ($ticketTypeId && ($type = TicketType::find($ticketTypeId))?->minimum_rank) {
            $minimumRank = $type->minimum_rank->value;
        }

        return User::whereHas('member', fn ($q) => $q
            ->where('rank', '>=', $minimumRank)
            ->whereNotNull('division_id')
            ->where('division_id', '!=', 0)
        )
            ->with('member')
            ->join('members', 'members.id', '=', 'users.member_id')
            ->orderBy('members.rank')
            ->orderBy('users.name')
            ->select('users.*')
            ->get()
            ->reject(fn ($u) => $excludeUserId && $u->id === $excludeUserId)
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'rank_name' => $u->member?->present()->rankName(),
                'avatar'    => $u->member?->getDiscordAvatarUrl(),
            ])
            ->values();
    }
}
