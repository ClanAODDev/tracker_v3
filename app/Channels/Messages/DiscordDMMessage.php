<?php

namespace App\Channels\Messages;

use Exception;

/**
 * Class DiscordMessage
 *
 * @package App\Channels
 */
class DiscordDMMessage
{

    private $target;
    private $message;

    /**
     * @param $target
     * @return DiscordDMMessage
     */
    public function to($target)
    {
        $this->target = $target;

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
     * @throws Exception
     */
    public function send()
    {
        if (!$this->target) {
            throw new Exception('A target user (snowflake, tag) must be defined');
        }

        if (!isset($this->message) && empty($this->fields)) {
            throw new Exception('A message must be defined');
        }

        return [
            'content' => "!relaydm {$this->target} {$this->message}"
        ];
    }
}