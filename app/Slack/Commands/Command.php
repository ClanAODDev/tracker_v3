<?php

namespace App\Slack\Commands;

/**
 * Interface Command
 *
 * @package App\Slack\Commands
 */
interface Command
{
    /**
     * Command constructor.
     *
     * @param $data
     */
    public function __construct($data);

    /**
     * Handle execution of command and return response method
     *
     * @return mixed
     */
    public function handle();

    /**
     * Provide a response to slack.
     *
     * @return mixed
     */
    public function response();
}
