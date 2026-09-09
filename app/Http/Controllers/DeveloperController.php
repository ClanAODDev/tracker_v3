<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
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
        return Inertia::render('developer/index', [
            'tokens' => $request->user()->tokens->map(fn ($token) => [
                'id'         => $token->id,
                'name'       => $token->name,
                'lastUsedAt' => $token->last_used_at?->diffForHumans(),
            ]),
            'plainTextToken' => $request->session()->get('token'),
        ]);
    }

    #[Authorize('create', NewAccessToken::class)]
    public function generateToken(Request $request): RedirectResponse
    {
        $request->validate(['token_name' => 'required']);

        $token = $request->user()->createToken($request->token_name, ['division:read']);

        $this->showSuccessToast('API token generated successfully!');

        return redirect(route('developer'))->with('token', $token->plainTextToken);
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
