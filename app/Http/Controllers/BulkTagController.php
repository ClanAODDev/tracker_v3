<?php

namespace App\Http\Controllers;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Policies\DivisionTagPolicy;
use Illuminate\Http\Request;

class BulkTagController extends Controller
{
    public function getTags(Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);
        $user = auth()->user();
        $policy = new DivisionTagPolicy;

        $availableTags = $policy->getAssignableTags($user, $member)
            ->get()
            ->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'visibility' => $tag->visibility->value,
                'global' => $tag->isGlobal(),
            ]);

        $memberTagIds = $member->tags()->pluck('division_tags.id')->toArray();

        return response()->json([
            'available' => $availableTags,
            'assigned' => $memberTagIds,
        ]);
    }

    public function addTag(Request $request, Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);

        $validated = $request->validate([
            'tag_id' => 'required|integer|exists:division_tags,id',
        ]);

        $user = auth()->user();
        $policy = new DivisionTagPolicy;

        $tag = $policy->getAssignableTags($user, $member)
            ->find($validated['tag_id']);

        if (! $tag) {
            return response()->json(['error' => 'Tag not found or not assignable'], 404);
        }

        $assignerId = $user->member?->id;
        $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $assignerId]]);

        return response()->json(['success' => true]);
    }

    public function createTag(Request $request, Division $division, Member $member)
    {
        $this->authorize('create', DivisionTag::class);
        $user = auth()->user();

        $userDivision = $user->isRole('admin') ? $division : $user->member?->division;
        if (! $userDivision) {
            return response()->json(['error' => 'No division found'], 403);
        }

        $validVisibilities = [TagVisibility::PUBLIC->value, TagVisibility::OFFICERS->value];
        if ($user->isRole(['admin', 'sr_ldr'])) {
            $validVisibilities[] = TagVisibility::SENIOR_LEADERS->value;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:25',
            'visibility' => 'nullable|string|in:' . implode(',', $validVisibilities),
        ]);

        $existingTag = $userDivision->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists'], 422);
        }

        $tag = $userDivision->tags()->create([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'] ?? TagVisibility::PUBLIC->value,
        ]);

        $assignerId = $user->member?->id;
        $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $assignerId]]);

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'visibility' => $tag->visibility->value,
            ],
        ]);
    }

    public function removeTag(Request $request, Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);

        $validated = $request->validate([
            'tag_id' => 'required|integer|exists:division_tags,id',
        ]);

        $member->tags()->detach($validated['tag_id']);

        return response()->json(['success' => true]);
    }

    public function createDivisionTag(Request $request, Division $division)
    {
        $this->authorize('create', DivisionTag::class);
        $user = auth()->user();

        $userDivision = $user->isRole('admin') ? $division : $user->member?->division;
        if (! $userDivision) {
            return response()->json(['error' => 'No division found'], 403);
        }

        $validVisibilities = [TagVisibility::PUBLIC->value, TagVisibility::OFFICERS->value];
        if ($user->isRole(['admin', 'sr_ldr'])) {
            $validVisibilities[] = TagVisibility::SENIOR_LEADERS->value;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:25',
            'visibility' => 'nullable|string|in:' . implode(',', $validVisibilities),
        ]);

        $existingTag = $userDivision->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists'], 422);
        }

        $tag = $userDivision->tags()->create([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'] ?? TagVisibility::PUBLIC->value,
        ]);

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'visibility' => $tag->visibility->value,
            ],
        ]);
    }

    public function create(Request $request, Division $division)
    {
        $this->authorize('assign', DivisionTag::class);

        $validated = $request->validate([
            'member-data' => 'required|string',
        ]);

        $memberIds = explode(',', $validated['member-data']);

        $members = Member::whereIn('clan_id', $memberIds)
            ->select('id', 'clan_id', 'name', 'rank')
            ->get();

        $user = auth()->user();
        $userDivisionId = $user->member?->division_id;

        $tags = $user->isRole('admin')
            ? DivisionTag::forDivision($division->id)->assignableBy($user)->get()
            : DivisionTag::forDivision($userDivisionId)->assignableBy($user)->get();

        return view('division.bulk-tags', compact('division', 'members', 'tags'));
    }

    public function edit(Division $division, Member $member)
    {
        $this->authorize('assign', [DivisionTag::class, $member]);

        $members = collect([$member]);
        $policy = new DivisionTagPolicy;
        $tags = $policy->getAssignableTags(auth()->user(), $member)->get();
        $returnTo = url()->previous();

        return view('division.bulk-tags', compact('division', 'members', 'tags', 'returnTo'));
    }

    public function store(Request $request, Division $division)
    {
        $this->authorize('assign', DivisionTag::class);

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer',
            'tags' => 'required|array',
            'tags.*' => 'integer|exists:division_tags,id',
            'action' => 'required|in:assign,remove',
        ]);

        $members = Member::whereIn('clan_id', $validated['member_ids'])->get();
        $user = auth()->user();
        $assignerId = $user->member?->id;

        foreach ($members as $member) {
            if (! $user->can('assign', [DivisionTag::class, $member])) {
                continue;
            }

            if ($validated['action'] === 'assign') {
                $pivotData = [];
                foreach ($validated['tags'] as $tagId) {
                    $pivotData[$tagId] = ['assigned_by' => $assignerId];
                }
                $member->tags()->syncWithoutDetaching($pivotData);
            } else {
                $member->tags()->detach($validated['tags']);
            }
        }

        $action = $validated['action'] === 'assign' ? 'assigned to' : 'removed from';
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
