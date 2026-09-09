<?php

namespace App\Http\Middleware;

use App\Models\TicketType;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth'  => fn () => $this->authProps($request),
            'nav'   => fn () => ($user = $request->user()) ? Navigation::for($user) : [],
            'flash' => fn () => [
                'toasts' => $request->session()->get('toastr::messages', []),
            ],
        ];
    }

    private function authProps(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return ['user' => null, 'permissions' => null];
        }

        return [
            'user' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'memberId'          => $user->member_id,
                'divisionId'        => $user->member?->division_id,
                'avatarUrl'         => $user->member?->getDiscordAvatarUrl(),
                'effectiveRole'     => $user->getEffectiveRole()?->getLabel(),
                'impersonating'     => (bool) $request->session()->get('impersonating'),
                'impersonatingRole' => $user->isImpersonatingRole(),
                'settings'          => [
                    'reduceAnimations' => (bool) $user->settings()->get('disable_animations', false),
                    'mobileNavSide'    => $user->settings()->get('mobile_nav_side', 'left'),
                    'theme'            => $user->settings()->get('theme', 'dark') === 'light' ? 'light' : 'dark',
                ],
            ],
            'permissions' => [
                'canWorkTickets' => TicketType::get()->contains(fn (TicketType $type) => $type->userCanWork($user)),
                'canUseBulkMode' => $user->isRole(['officer', 'sr_ldr', 'admin']) || $user->isDeveloper(),
                'isAdmin'        => $user->isRole('admin'),
                'isDeveloper'    => $user->isDeveloper(),
            ],
        ];
    }
}
