<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kirschbaum\Commentions\Comment;

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
            ->withCount('comments')
            ->latest()
            ->get()
            ->map(fn ($app) => [
                'id'               => $app->id,
                'discord_username' => $app->user->discord_username,
                'created_at'       => $app->created_at->toIso8601String(),
                'comments_count'   => $app->comments_count,
                'responses'        => collect($app->responses)->map(fn ($response) => [
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

        $application->load(['user', 'comments.author.member']);

        return response()->json([
            'application' => [
                'id'               => $application->id,
                'discord_username' => $application->user->discord_username,
                'created_at'       => $application->created_at->toIso8601String(),
                'responses'        => collect($application->responses)->map(fn ($response) => [
                    'label' => $response['label'] ?? 'Unknown',
                    'value' => is_array($response['value'] ?? null)
                        ? implode(', ', $response['value'])
                        : ($response['value'] ?: '—'),
                ])->values(),
                'comments' => $application->comments->map(fn ($comment) => [
                    'id'   => $comment->id,
                    'body' => $comment->body,
                    'user' => $comment->author ? [
                        'id'   => $comment->author->id,
                        'name' => $comment->author->member?->present()->rankName() ?? $comment->author->name,
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

        $comment = $application->comment($validated['body'], auth()->user());

        $comment->load('author.member');

        return response()->json([
            'comment' => [
                'id'   => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id'   => $comment->author->id,
                    'name' => $comment->author->member?->present()->rankName() ?? $comment->author->name,
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
            'message' => 'Comment added',
        ], 201);
    }

    public function deleteComment(Division $division, DivisionApplication $application, Comment $comment): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        if ((int) $comment->author_id !== (int) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
