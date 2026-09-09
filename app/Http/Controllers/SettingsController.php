<?php

namespace App\Http\Controllers;

use App\Filament\Forms\Components\IngameHandlesForm;
use App\Http\Requests\Member\SyncDiscordAvatar;
use App\Models\Division;
use App\Models\Handle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Throwable;

#[Middleware('auth')]
class SettingsController extends Controller
{
    private const EXCLUDED_DIVISIONS = ['Floater', "Bluntz' Reserves"];

    public function data(Request $request): JsonResponse
    {
        $user     = $request->user();
        $member   = $user->member;
        $settings = $user->settings();

        $payload = [
            'settings' => [
                'disable_animations' => (bool) $settings->get('disable_animations', false),
                'mobile_nav_side'    => $settings->get('mobile_nav_side', 'left'),
                'theme'              => $settings->get('theme', 'dark'),
            ],
            'member' => null,
        ];

        if ($member) {
            $excludedIds = Division::whereIn('name', self::EXCLUDED_DIVISIONS)->pluck('id')->all();
            $pending     = $member->transfers()->pending()->with('division:id,name')->first();

            $payload['member'] = [
                'name'          => $member->name,
                'avatarUrl'     => $member->getDiscordAvatarUrl(),
                'canSyncAvatar' => $member->discord_id !== null,
                'division'      => $member->division
                    ? ['name' => $member->division->name, 'logo' => $member->division->getLogoPath()]
                    : null,
                'canRequestTransfer' => (bool) $member->division_id && $member->division?->name !== 'Floater',
            ];

            $payload['pendingTransfer'] = $pending ? ['division' => $pending->division->name] : null;

            $payload['transferableDivisions'] = Division::active()
                ->whereNull('shutdown_at')
                ->whereNotIn('id', $excludedIds)
                ->where('id', '!=', $member->division_id)
                ->orderBy('name')
                ->get(['id', 'name']);

            $payload['partTimeDivisions'] = [
                'available' => Division::active()
                    ->whereNotIn('id', $excludedIds)
                    ->when($member->division_id, fn ($q) => $q->where('id', '!=', $member->division_id))
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'selected' => $member->partTimeDivisions()->pluck('divisions.id')->all(),
            ];

            $payload['handles'] = [
                'types'   => Handle::orderBy('label')->get(['id', 'label']),
                'current' => $member->memberHandles()->with('handle:id,label')->get()->map(fn ($mh) => [
                    'id'       => $mh->id,
                    'handleId' => $mh->handle_id,
                    'value'    => $mh->value,
                    'primary'  => (bool) $mh->primary,
                ]),
            ];
        }

        return response()->json($payload);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'disable_animations' => ['sometimes', 'boolean'],
            'mobile_nav_side'    => ['sometimes', 'in:left,right'],
            'theme'              => ['sometimes', 'in:light,dark'],
        ]);

        $user           = $request->user();
        $user->settings = array_merge($user->settings, $validated);
        $user->save();

        return response()->json(['success' => true]);
    }

    public function partTimeDivisions(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            return response()->json(['error' => 'No member record'], 400);
        }

        $activeIds = Division::active()->pluck('id')->all();
        $validIds  = array_values(array_intersect($request->input('divisions', []), $activeIds));
        $member->partTimeDivisions()->sync($validIds);

        return response()->json(['success' => true, 'count' => count($validIds)]);
    }

    public function ingameHandles(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            return response()->json(['error' => 'No member record'], 400);
        }

        IngameHandlesForm::saveHandles($member, $request->input('handles', []));

        return response()->json(['success' => true, 'count' => $member->memberHandles()->count()]);
    }

    public function syncAvatar(SyncDiscordAvatar $request): JsonResponse
    {
        try {
            $request->persist();
        } catch (Throwable) {
            return response()->json(['message' => 'Failed to reach Discord bot'], 503);
        }

        return response()->json(['avatarUrl' => $request->user()->member->fresh()->getDiscordAvatarUrl()]);
    }
}
