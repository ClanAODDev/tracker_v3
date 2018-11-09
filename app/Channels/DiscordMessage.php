<?php

namespace App\Channels;

/**
 * Class DiscordMessage
 *
 * @package App\Channels
 */
class DiscordMessage
{

    /**
     * Color codes
     */
    CONST SUCCESS = 3066993;
    CONST ERROR = 15158332;
    CONST INFO = 10181046;

    /**
     * Use info-coded color
     *
     * @return $this
     */
    public function info()
    {
        $this->color = self::INFO;

        return $this;
    }

    /**
     * Use success-code color
     */
    public function success()
    {
        $this->color = self::SUCCESS;

        return $this;
    }

    /**
     * Use danger-code color
     */
    public function error()
    {
        $this->color = self::ERROR;

        return $this;
    }

    /**
     * @param $channel
     * @return DiscordMessage
     */
    public function to($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function send()
    {
        if ( ! $this->channel) {
            throw new \Exception('A channel must be defined');
        }

        if ( ! $this->message) {
            throw new \Exception('A message must be defined');
        }

        if ( ! $this->fields) {
            return [
                'content' => "!relay {$this->channel} {$this->message}"
            ];
        }

        $body = ! $this->fields
            ? $this->message
            : json_encode([
                'embed' => [
                    'title' => $this->message,
                    'author' => [
                        'name' => 'AOD Tracker'
                    ],
                    'color' => $this->color ?? 0,
                    'fields' => $this->fields ?? []
                ]
            ]);

        return [
            'content' => "! relay {$this->channel} {$body}"
        ];
    }
}