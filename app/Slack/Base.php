<?php

namespace App\Slack;

use GuzzleHttp\Client;

/**
 * Base class for Slack commands
 *
 * @package App\Slack\Commands
 */
class Base
{
    /**
     * Useful if response will take longer than 300ms
     *
     * @param $message
     */
    protected function delayedResponse($message)
    {
        $client = new Client;

        $client->post($this->data['response_url'], [
            'json' => [
                'text' => $message
            ]
        ]);
    }
}
