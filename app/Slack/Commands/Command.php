<?php

namespace App\Slack\Commands;

interface Command
{
    public function __construct($data);

    public function handle();

    public function response();
}
