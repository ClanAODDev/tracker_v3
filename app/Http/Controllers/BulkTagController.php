<?php

namespace App\Http\Controllers;

use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Http\Requests\DivisionTag\AddTagRequest;
use App\Http\Requests\DivisionTag\CreateBulkTagPageRequest;
use App\Http\Requests\DivisionTag\CreateDivisionTagRequest;
use App\Http\Requests\DivisionTag\RemoveTagRequest;
use App\Http\Requests\DivisionTag\StoreBulkTagRequest;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Policies\DivisionTagPolicy;

class BulkTagController extends Controller
{
    public function getTags(Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);
        $user   = auth()->user();
        $policy = new DivisionTagPolicy;

        $availableTags = $policy->getAssignableTags($user)
            ->get()
            ->map(fn ($tag) => [
                'id'         => $tag->id,
                'name'       => $tag->name,
                'visibility' => $tag->visibility->value,
                'global'     => $tag->isGlobal(),
            ]);

        $memberTagIds = $member->tags()->pluck('division_tags.id')->toArray();

        return response()->json([
            'available' => $availableTags,
            'assigned'  => $memberTagIds,
        ]);
    }

    public function addTag(AddTagRequest $request, Division $division, Member $member)
    {
        $validated = $request->validated();

        $user   = auth()->user();
        $policy = new DivisionTagPolicy;

        $tag = $policy->getAssignableTags($user)
            ->find($validated['tag_id']);

        if (! $tag) {
            return response()->json(['error' => 'Tag not found or not assignable'], 404);
        }

        $assignerId = $user->member?->id;
        $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $assignerId]]);

        return response()->json(['success' => true]);
    }

    public function createTag(CreateDivisionTagRequest $request, Division $division, Member $member)
    {
        $user = auth()->user();

        $userDivision = $user->isRole('admin') ? $division : $user->member?->division;
        if (! $userDivision) {
            return response()->json(['error' => 'No division found'], 403);
        }

        $validated = $request->validated();

        $existingTag = $userDivision->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists'], 422);
        }

        $tag = $userDivision->tags()->create([
            'name'       => $validated['name'],
            'visibility' => $validated['visibility'] ?? TagVisibility::PUBLIC->value,
        ]);

        $assignerId = $user->member?->id;
        $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $assignerId]]);

        return response()->json([
            'success' => true,
            'tag'     => [
                'id'         => $tag->id,
                'name'       => $tag->name,
                'visibility' => $tag->visibility->value,
            ],
        ]);
    }

    public function removeTag(RemoveTagRequest $request, Division $division, Member $member)
    {
        $member->tags()->detach($request->validated('tag_id'));

        return response()->json(['success' => true]);
    }

    public function createDivisionTag(CreateDivisionTagRequest $request, Division $division)
    {
        $user = auth()->user();

        $userDivision = $user->isRole('admin') ? $division : $user->member?->division;
        if (! $userDivision) {
            return response()->json(['error' => 'No division found'], 403);
        }

        $validated = $request->validated();

        $existingTag = $userDivision->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists'], 422);
        }

        $tag = $userDivision->tags()->create([
            'name'       => $validated['name'],
            'visibility' => $validated['visibility'] ?? TagVisibility::PUBLIC->value,
        ]);

        return response()->json([
            'success' => true,
            'tag'     => [
                'id'         => $tag->id,
                'name'       => $tag->name,
                'visibility' => $tag->visibility->value,
            ],
        ]);
    }

    public function create(CreateBulkTagPageRequest $request, Division $division)
    {
        $memberIds = explode(',', $request->validated('member-data'));

        $members = Member::whereIn('clan_id', $memberIds)
            ->select('id', 'clan_id', 'name', 'rank')
            ->get();

        $user           = auth()->user();
        $userMember     = $user->member;
        $userDivisionId = $userMember?->division_id;
        $isSgt          = $userMember?->isAtLeast(Rank::SERGEANT) ?? false;

        $tags = match (true) {
            $user->isRole('admin') => DivisionTag::forDivision($division->id)->assignableBy($user)->get(),
            $isSgt                 => DivisionTag::assignableBy($user)->get(),
            default                => DivisionTag::forDivision($userDivisionId)->assignableBy($user)->get(),
        };

        return view('division.bulk-tags', compact('division', 'members', 'tags'));
    }

    public function edit(Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);

        $members  = collect([$member]);
        $policy   = new DivisionTagPolicy;
        $tags     = $policy->getAssignableTags(auth()->user())->get();
        $returnTo = url()->previous();

        return view('division.bulk-tags', compact('division', 'members', 'tags', 'returnTo'));
    }

    public function store(StoreBulkTagRequest $request, Division $division)
    {
        $validated = $request->validated();

        $members    = Member::whereIn('clan_id', $validated['member_ids'])->get();
        $user       = auth()->user();
        $assignerId = $user->member?->id;

        $policy = new DivisionTagPolicy;
        $tagIds = $policy->getAssignableTags($user)
            ->whereIn('id', $validated['tags'])
            ->pluck('id')
            ->all();

        foreach ($members as $member) {
            if (! $user->can('assign', [DivisionTag::class, $member]) || empty($tagIds)) {
                continue;
            }

            if ($validated['action'] === 'assign') {
                $pivotData = [];
                foreach ($tagIds as $tagId) {
                    $pivotData[$tagId] = ['assigned_by' => $assignerId];
                }
                $member->tags()->syncWithoutDetaching($pivotData);
            } else {
                $member->tags()->detach($tagIds);
            }
        }

        $action  = $validated['action'] === 'assign' ? 'assigned to' : 'removed from';
        $message = 'Tags ' . $action . ' ' . $members->count() . ' ' . ($members->count() === 1 ? 'member' : 'members') . '.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        if ($request->has('return_to')) {
            return redirect($request->input('return_to'))->with('success', $message);
        }

        return redirect()
            ->route('division.members', $division)
            ->with('success', $message);
    }
}
