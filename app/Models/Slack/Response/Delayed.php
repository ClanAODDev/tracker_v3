<?php

namespace App\Models\Slack\Response;

use GuzzleHttp\Client;

class Delayed
{
    /**
     * Useful if response will take longer than 300ms.
     *
     * @param $message
     * @param $data
     */
    public static function handle($message, $data)
    {
        $client = new Client();

        if (!empty($data['response_url'])) {
            $client->post($data['response_url'], [
                'json' => [
                    'text' => $message,
                ],
            ]);
        }
    }
}
