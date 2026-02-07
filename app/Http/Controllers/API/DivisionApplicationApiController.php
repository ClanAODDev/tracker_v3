<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Parallax\FilamentComments\Models\FilamentComment;

class DivisionApplicationApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Division $division): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        if (! $division->settings()->get('application_required', false)) {
            return response()->json(['applications' => []]);
        }

        $applications = DivisionApplication::pending()
            ->where('division_id', $division->id)
            ->with('user')
            ->withCount('filamentComments')
            ->latest()
            ->get()
            ->map(fn ($app) => [
                'id' => $app->id,
                'discord_username' => $app->user->discord_username,
                'created_at' => $app->created_at->toIso8601String(),
                'comments_count' => $app->filament_comments_count,
                'responses' => collect($app->responses)->map(fn ($response) => [
                    'label' => $response['label'] ?? 'Unknown',
                    'value' => is_array($response['value'] ?? null)
                        ? implode(', ', $response['value'])
                        : ($response['value'] ?: '—'),
                ])->values(),
            ]);

        return response()->json(['applications' => $applications]);
    }

    public function show(Division $division, DivisionApplication $application): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        $application->load(['user', 'filamentComments.user.member']);

        return response()->json([
            'application' => [
                'id' => $application->id,
                'discord_username' => $application->user->discord_username,
                'created_at' => $application->created_at->toIso8601String(),
                'responses' => collect($application->responses)->map(fn ($response) => [
                    'label' => $response['label'] ?? 'Unknown',
                    'value' => is_array($response['value'] ?? null)
                        ? implode(', ', $response['value'])
                        : ($response['value'] ?: '—'),
                ])->values(),
                'comments' => $application->filamentComments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'body' => $comment->comment,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->member?->present()->rankName() ?? $comment->user->name,
                    ] : null,
                    'created_at' => $comment->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function destroy(Division $division, DivisionApplication $application): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        if (! auth()->user()->isRole(['sr_ldr', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $application->delete();

        return response()->json(['message' => 'Application deleted']);
    }

    public function addComment(Request $request, Division $division, DivisionApplication $application): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        $validated = $request->validate([
            'body' => 'required|string|min:5',
        ]);

        $comment = $application->filamentComments()->create([
            'user_id' => auth()->id(),
            'subject_type' => $application->getMorphClass(),
            'comment' => $validated['body'],
        ]);

        $comment->load('user.member');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->comment,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->member?->present()->rankName() ?? $comment->user->name,
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
            'message' => 'Comment added',
        ], 201);
    }

    public function deleteComment(Division $division, DivisionApplication $application, FilamentComment $comment): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        if ((int) $comment->user_id !== (int) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
