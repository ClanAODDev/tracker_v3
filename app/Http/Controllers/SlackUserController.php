<?php

namespace App\Http\Controllers;

use App\User;
use CL\Slack\Payload\UsersListPayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Http\Request;

class SlackUserController extends Controller
{
    public function __construct()
    {
        $this->client = new ApiClient(config('services.slack.token'));
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
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

        return view('slack.users', compact('users', 'matchingCount'));
    }
}
