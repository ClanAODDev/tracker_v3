<?php

namespace App\Http\Controllers;

use App\Http\Requests\Member\SendBulkPm;
use App\Models\Member;
use Inertia\Inertia;
use Inertia\Response;

class PmController extends Controller
{
    public function create(SendBulkPm $request): Response
    {
        $memberIds = explode(',', $request->validated()['pm-member-data']);

        $selected = Member::whereIn('clan_id', $memberIds)
            ->select('clan_id', 'allow_pm', 'name')
            ->get();

        $available = $selected->filter->allow_pm;
        $division  = $request->division;

        $groups = $available->pluck('clan_id')
            ->values()
            ->chunk(20)
            ->values()
            ->map(fn ($chunk, $index) => [
                'label' => 'Group ' . ($index + 1),
                'url'   => doForumFunction($chunk->values()->all(), 'pm'),
            ]);

        return Inertia::render('division/create-pm', [
            'division'       => ['name' => $division->name, 'slug' => $division->slug],
            'groups'         => $groups,
            'recipientCount' => $available->count(),
            'omitted'        => $selected->diffAssoc($available)->pluck('name')->values(),
            'canRemind'      => $request->user()->can('remindActivity', Member::class),
            'reminderUrl'    => route('bulk-reminder.store', $division),
            'reminderIds'    => $available->pluck('clan_id')->values(),
        ]);
    }
}
