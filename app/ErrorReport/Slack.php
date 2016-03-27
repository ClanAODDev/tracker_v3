<?php

namespace App\ErrorReport;

use Maknz\Slack\Facades\Slack as SlackClient;

class Slack
{
    /**
     * @param $message
     */
    public static function send($message)
    {
        SlackClient::attach([
            'text' => $message,
            'color' => 'danger',
        ])->send('*An error occurred!*');
    }


}