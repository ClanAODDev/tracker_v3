<?php

namespace App\Reports;

use Maknz\Slack\Facades\Slack as SlackClient;

class Slack
{
    /**
     * Send slack an error message
     *
     * @param $message
     */
    public static function error($message)
    {
        SlackClient::attach([
            'text' => $message,
            'color' => 'danger',
        ])->send('*An error occurred!*');
    }

    /**
     * Send slack a general message
     *
     * @param $message
     */
    public static function info($message)
    {
        SlackClient::send($message);
    }
}
