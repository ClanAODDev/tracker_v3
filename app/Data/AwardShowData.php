<?php

namespace App\Data;

use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AwardShowData
{
    private LengthAwarePaginator $recipients;

    private bool $userHasAward;

    private ?Member $userMember;

    public function __construct(private Award $award)
    {
        $award->load(['division']);
        $award->loadCount('recipients');

        $recipientsQuery = MemberAward::where('award_id', $award->id)
            ->where('approved', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->with(['member:id,clan_id,name,rank,division_id,discord_id,discord_avatar', 'member.division:id,name,slug']);

        $this->recipients = $award->repeatable
            ? $recipientsQuery
                ->selectRaw('member_id, COUNT(*) as times_received, MAX(created_at) as last_awarded_at')
                ->groupBy('member_id')
                ->orderByDesc('times_received')
                ->paginate(50)
            : $recipientsQuery->orderByDesc('created_at')->paginate(50);

        $this->userMember = auth()->user()?->member;

        $this->userHasAward = $this->userMember
            ? MemberAward::where('award_id', $award->id)
                ->where('member_id', $this->userMember->id)
                ->where('approved', true)
                ->exists()
            : false;
    }

    public static function for(Award $award): self
    {
        return new self($award);
    }

    public function toArray(): array
    {
        $award      = $this->award;
        $recipients = $this->recipients;

        return [
            'award' => [
                'id'           => $award->id,
                'name'         => $award->name,
                'description'  => $award->description,
                'rarity'       => $award->getRarity(),
                'image'        => $award->image ? $award->getImagePath() : null,
                'repeatable'   => (bool) $award->repeatable,
                'allowRequest' => (bool) $award->allow_request,
                'canRequest'   => $award->canBeRequestedBy(),
                'division'     => $award->division
                    ? ['name' => $award->division->name, 'slug' => $award->division->slug, 'active' => (bool) $award->division->active]
                    : null,
            ],
            'stats' => [
                'total'        => $recipients->total(),
                'firstAwarded' => MemberAward::where('award_id', $award->id)->where('approved', true)
                    ->orderBy('created_at')->first()?->created_at?->format('M Y'),
                'lastAwarded' => MemberAward::where('award_id', $award->id)->where('approved', true)
                    ->orderByDesc('created_at')->first()?->created_at?->format('M Y'),
                'rarity' => $award->getRarity(),
            ],
            'userHasAward'  => $this->userHasAward,
            'currentMember' => $this->userMember
                ? ['name' => $this->userMember->name, 'clanId' => $this->userMember->clan_id]
                : null,
            'recipients' => [
                'data' => collect($recipients->items())->map(fn ($record) => [
                    'name'        => $record->member?->name,
                    'url'         => $record->member ? route('member', $record->member->getUrlParams()) : null,
                    'avatarUrl'   => $record->member?->getDiscordAvatarUrl(),
                    'division'    => $record->member?->division?->name,
                    'divisionUrl' => $record->member?->division
                        ? route('division', $record->member->division->slug)
                        : null,
                    'rank'          => $record->member?->rank?->getAbbreviation(),
                    'timesReceived' => $award->repeatable ? (int) $record->times_received : null,
                    'awardedAt'     => $award->repeatable
                        ? Carbon::parse($record->last_awarded_at)->format('M j, Y')
                        : $record->created_at->format('M j, Y'),
                ]),
                'currentPage' => $recipients->currentPage(),
                'lastPage'    => $recipients->lastPage(),
            ],
        ];
    }
}
