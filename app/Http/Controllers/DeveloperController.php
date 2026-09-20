<?php

namespace App\Http\Controllers;

use App\Enums\ApiScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\NewAccessToken;

#[Middleware('auth')]
#[Middleware('developer')]
class DeveloperController extends Controller
{
    #[Authorize('create', NewAccessToken::class)]
    public function index(Request $request): Response
    {
        $availableScopes = ApiScope::availableFor($request->user());

        return Inertia::render('developer/index', [
            'tokens' => $request->user()->tokens->map(fn ($token) => [
                'id'         => $token->id,
                'name'       => $token->name,
                'lastUsedAt' => $token->last_used_at?->diffForHumans(),
                'scopes'     => $token->abilities,
            ]),
            'plainTextToken'  => $request->session()->get('token'),
            'availableScopes' => array_map(fn (ApiScope $scope) => [
                'value'       => $scope->value,
                'label'       => $scope->label(),
                'description' => $scope->description(),
            ], $availableScopes),
        ]);
    }

    #[Authorize('create', NewAccessToken::class)]
    public function generateToken(Request $request): RedirectResponse
    {
        $allowedScopes = array_map(fn (ApiScope $scope) => $scope->value, ApiScope::availableFor($request->user()));

        $request->validate([
            'token_name' => 'required',
            'scopes'     => 'array',
            'scopes.*'   => [Rule::in($allowedScopes)],
        ]);

        $scopes = $request->input('scopes', []) ?: ['division:read'];

        $token = $request->user()->createToken($request->token_name, $scopes);

        $this->showSuccessToast('API token generated successfully!');

        return redirect(route('developer'))->with('token', $token->plainTextToken);
    }

    public function updateTokenScopes(Request $request): RedirectResponse
    {
        $allowedScopes = array_map(fn (ApiScope $scope) => $scope->value, ApiScope::availableFor($request->user()));

        $request->validate([
            'token_id' => 'required',
            'scopes'   => 'array',
            'scopes.*' => [Rule::in($allowedScopes)],
        ]);

        $token = $request->user()->tokens()->where('id', $request->token_id)->firstOrFail();

        $this->authorize('destroy', [NewAccessToken::class, $token]);

        $token->forceFill(['abilities' => $request->input('scopes', [])])->save();

        $this->showSuccessToast('Token scopes updated!');

        return redirect(route('developer'));
    }

    public function destroyToken(Request $request): RedirectResponse
    {
        $this->authorize('destroy', [NewAccessToken::class, $request->token_id]);

        $request->validate(['token_id' => 'required']);

        auth()->user()->tokens()->where('id', $request->token_id)->delete();

        $this->showSuccessToast('API token deleted!');

        return redirect(route('developer'));
    }
}
