<?php

namespace App\Slack;

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

    public function __construct($data)
    {
        $params = last(
            explode(':', $data['text'], 2)
        );

        $this->params = trim($params);
    }
}
