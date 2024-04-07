<?php

namespace App\Channels\Messages;

use Exception;

class BotMessage
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

    public function title($title)
    {
        $this->title = $title;

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
     * @throws Exception
     */
    public function fields($fields)
    {
        foreach ($fields as $field) {
            if (!array_key_exists('name', $field) || (!array_key_exists('value', $field))) {
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
     * @return array
     *
     * @throws Exception
     */
    public function send(): array
    {
        if (!isset($this->message) || !isset($this->title)) {
            throw new Exception('A title and message must be defined');
        }

        /**
         * https://discordjs.guide/popular-topics/embeds.html#using-an-embed-object
         *
         * Example of an error discord message
         *
         **** (new BotMessage())->title('Something bad happened')
         **** ->message('Your approval could not be processed')
         **** ->url('relevant/error/page/here')
         **** ->error()
         */
        return [
            'embeds' => [
                'color' => $this->color ?? 0,
                'description' => $this->message,
                'author' => [
                    'name' => $this->title,
                    'icon_url' => 'http://tracker.clanaod.net/images/logo_v2.png',
                    'url' => $this->url ?? 'https://tracker.clanaod.net',
                ],
                'fields' => $this->fields ?? [],
            ],
        ];
    }
}
