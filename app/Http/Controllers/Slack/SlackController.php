<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
     * @throws \Exception
     */
    protected function getSlackErrorResponse($response)
    {
        if (! isset($response['error'])) {
            $this->showErrorToast('Something went terribly wrong.');

            return redirect()->route('home');
        }

        throw new \Exception('Slack error code: ' . $response['error']);
    }
}
