<?php

namespace App;

class Slack
{
    /**
     * Checks to see if a class for a particular command exists,
     * and executes the command if it does.
     *
     * @param $command
     * @param array $data
     * @return array
     */
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
