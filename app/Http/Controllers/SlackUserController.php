<?php

namespace App\Http\Controllers;

use App\User;
use CL\Slack\Payload\UsersListPayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Http\Request;

class SlackUserController extends Controller
{
    private $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('manageSlack', auth()->user());

        $payload = new UsersListPayload();
        $response = $this->client->send($payload);
        $emails = $response->getUsers();

        $results = collect($emails)->map(function ($user) {
            if ( ! $user->isDeleted() && $user->getProfile()->getEmail() !== null) {
                return $user->getProfile()->getEmail();
            }
        })->flatten()->filter(function ($email) {
            return $email !== null;
        });

        $matchingCount = collect($results)->count();
        $users = User::whereIn('email', $results)->with('member.rank', 'member.position')->get();

        if (auth()->user()->isRole('sr_ldr')) {
            $users = $users->filter(function ($user) {
                return $user->member->division_id === auth()->user()->member->division_id;
            });
        }

        return view('slack.users', compact('users', 'matchingCount'));
    }
}
