<?php

namespace App\Channels\Messages;

use Exception;

class BotChannelMessage
{
    /**
     * Message color codes - only relevant to embeds
     */
    private const SUCCESS = 3066993;

    private const ERROR = 15158332;

    private const INFO = 10181046;

    private $fields = [];

    private mixed $message;

    private int $color;

    private $target;

    private $thumbnail = [];

    public function __construct(private $notifiable)
    {
    }

    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    public function thumbnail($thumbnail)
    {
        $this->thumbnail = ['url' => $thumbnail];

        return $this;
    }

    /**
     * Use info-coded color.
     *
     * @return $this
     */
    public function info()
    {
        $this->color = self::INFO;

        return $this;
    }

    /**
     * Use success-code color.
     */
    public function success()
    {
        $this->color = self::SUCCESS;

        return $this;
    }

    /**
     * Use danger-code color.
     */
    public function error()
    {
        $this->color = self::ERROR;

        return $this;
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    public function fields($fields)
    {
        foreach ($fields as $field) {
            if (! array_key_exists('name', $field) || (! array_key_exists('value', $field))) {
                throw new \Exception('Fields must include a name and value pair');
            }
        }

        $this->fields = $fields;

        return $this;
    }

    /**
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return $this
     */
    public function target($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function send(): array
    {
        if (! isset($this->title)) {
            throw new Exception('A title must be defined');
        }

        if (! isset($this->message) && ! isset($this->fields)) {
            throw new Exception('A message or fields must be defined');
        }

        $routeTarget = $this->notifiable->routeNotificationFor('bot');
        if (! isset($routeTarget) && ! isset($this->target)) {
            throw new Exception('A channel target must be defined');
        }

        $message = [
            'api_uri' => sprintf('channels/%s', $routeTarget ?? $this->target),
            'body' => [
                'embeds' => [[
                    'color' => $this->color ?? 0,
                    'description' => $this->message ?? '',
                    'author' => [
                        'name' => $this->title,
                        'icon_url' => asset('images/logo_v2.png'),
                        'url' => $this->url ?? config('app.url'),
                    ],
                    'fields' => $this->fields ?? [],
                ]],
            ],
        ];

        if ($this->thumbnail) {
            $message['body']['embeds'][0]['thumbnail'] = $this->thumbnail;
        }

        /**
         * https://discordjs.guide/popular-topics/embeds.html#using-an-embed-object
         *
         * Example of an error discord message
         *
         **** (new BotChannelMessage($notifiable))->title('Something bad happened')
         **** ->message('Your approval could not be processed')
         **** ->url('relevant/error/page/here')
         **** ->error()
         */
        return $message;
    }
}
