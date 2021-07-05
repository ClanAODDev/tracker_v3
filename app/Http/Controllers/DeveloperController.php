<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;

class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $this->authorize('create', NewAccessToken::class);

        $tokens = $request->user()->tokens;

        return view('developer.index', compact('tokens'));
    }

    public function generateToken(Request $request)
    {
        $this->authorize('create', NewAccessToken::class);

        request()->validate([
            'token_name' => 'required',
        ]);

        $token = $request->user()->createToken($request->token_name);

        $this->showToast('API token generated successfully!');

        return redirect(route('developer'))->with(['token' => $token->plainTextToken]);
    }

    public function destroyToken(Request $request)
    {
        $this->authorize('destroy', [NewAccessToken::class, $request->token_id]);

        request()->validate([
            'token_id' => 'required',
        ]);

        auth()->user()->tokens()->where('id', $request->token_id)->delete();

        $this->showToast('API token deleted!');

        return redirect(route('developer'));
    }
}
