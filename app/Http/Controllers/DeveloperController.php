<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Laravel\Sanctum\NewAccessToken;

#[Middleware('auth')]
#[Middleware('developer')]
class DeveloperController extends Controller
{
    #[Authorize('create', NewAccessToken::class)]
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens;

        return view('developer.index', compact('tokens'));
    }

    #[Authorize('create', NewAccessToken::class)]
    public function generateToken(Request $request)
    {

        request()->validate([
            'token_name' => 'required',
        ]);

        $token = $request->user()->createToken($request->token_name, ['division:read']);

        $this->showSuccessToast('API token generated successfully!');

        return redirect(route('developer'))->with(['token' => $token->plainTextToken]);
    }

    public function destroyToken(Request $request)
    {
        $this->authorize('destroy', [NewAccessToken::class, $request->token_id]);

        request()->validate([
            'token_id' => 'required',
        ]);

        auth()->user()->tokens()->where('id', $request->token_id)->delete();

        $this->showSuccessToast('API token deleted!');

        return redirect(route('developer'));
    }
}
