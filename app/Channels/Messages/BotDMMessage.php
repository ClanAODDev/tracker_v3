<?php

namespace App\Channels\Messages;

class BotDMMessage
{
    private $target;

    private $message;

    public function to($target)
    {
        $this->target = $target;

        return $this;
    }

    public function message($message)
    {
        $this->message = addslashes($message);

        return $this;
    }

    public function send(): array
    {
        if (! $this->target || ! $this->message) {
            return [];
        }

        return [
            'api_uri' => sprintf('members/%s', $this->target),
            'body'    => [
                'embeds' => [[
                    'description' => $this->message,
                ]],
            ],
        ];
    }
}
