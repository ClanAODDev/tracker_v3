<?php

namespace App\Models\Slack;

/**
 * Interface Command.
 */
interface Command
{
    /**
     * Command constructor.
     */
    public function __construct($data);

    /**
     * Handle execution of command and return response method.
     *
     * @return mixed
     */
    public function handle();
}
