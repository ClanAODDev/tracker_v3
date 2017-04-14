<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 5/17/16
 * Time: 9:46 AM
 */

namespace App\Slack\Response;

use GuzzleHttp\Client;

class Delayed
{
    /**
     * Useful if response will take longer than 300ms
     *
     * @param $message
     * @param $data
     */
    public static function handle($message, $data)
    {
        $client = new Client;

        if ( ! empty($data['response_url'])) {
            $client->post($data['response_url'], [
                'json' => [
                    'text' => $message
                ]
            ]);
        }
    }
}
