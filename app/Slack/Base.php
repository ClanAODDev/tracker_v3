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
     * All commands have parameters
     *
     * @var array
     */
    protected $params;

    public function __construct($data) {
        $params = last(
            explode(':', $data['text'], 2)
        );

        $this->params = trim($params);
    }

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
