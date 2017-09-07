<?php

namespace App\Http\Controllers\Slack;

use App\User;
use Illuminate\Http\Request;
use wrapi\slack\slack;

class SlackUserController extends SlackController
{
    private $client;

    public function __construct(slack $client)
    {
        $this->client = $client;
        $this->middleware('auth');
    }

    /**
     * @return SlackChannelController|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {

        // TODO: Add slack error handling here

        $this->authorize('manageSlack', auth()->user());

        $response = $this->client->users->list();

        if ( ! $response['ok']) {
            return $this->getSlackErrorResponse($response);
        }

        // omit disabled accounts, slackbot
        $results = collect($response['members'])->map(function ($user) {
            if ( ! $user['deleted'] && $user['name'] !== 'slackbot') {
                return $user['profile']['email'];
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
