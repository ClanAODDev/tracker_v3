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
        if ( ! isset($response['error'])) {
            $this->showErrorToast('Something went terribly wrong.');

            return redirect()->route('home');
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

        $this->showErrorToast('Something went terribly wrong. Slack error code: ' . $response['error']);

        return redirect()->route('home');
    }
}
