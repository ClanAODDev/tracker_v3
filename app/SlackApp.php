<?php

namespace App;

class SlackApp
{
    public static function handle($command, array $data)
    {
        $command = sprintf('App\Slack\Commands\%s', studly_case($command));

        if (class_exists($command)) {
            $command = new $command($data);

            return $command->handle();
        }

        return [
            "text" => 'Unrecognized command. Sorry!',
        ];
    }
}
