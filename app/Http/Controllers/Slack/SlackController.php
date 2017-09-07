<?php

namespace App\Http\Controllers\Slack;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class SlackController extends Controller
{
    /**
     * @param $message
     */
    protected function logSlackAction($message)
    {
        Log::info(auth()->user()->name . $message . Carbon::now());
    }

    /**
     * @param $response
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    protected function getSlackErrorResponse($response)
    {
        dd($response);
        if ( ! isset($response['error'])) {
            redirect()->back()->withErrors([
                'slack-error' => "Error information not returned",
            ])->withInput();
        }

        switch ($response['error']) {
            case 'name_taken':
                $message = "Name taken";
                break;
            case 'channel_not_found':
                $message = "Channel not found";
                break;
            default:
                $message = "Unknown slack error";
                break;
        }

        return redirect()->back()->withErrors([
            'slack-error' => $message,
        ])->withInput();
    }
}
