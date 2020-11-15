<?php

namespace App\Models\Slack;

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
}
