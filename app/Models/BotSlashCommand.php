<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BotSlashCommand
{
    /**
     * Checks to see if a class for a particular command exists,
     * and executes the command if it does.
     *
     * @return array
     */
    public static function handle($command, Request $request)
    {
        $command = sprintf('App\Models\Bot\Commands\%s', Str::studly($command));

        if (class_exists($command)) {
            $command = new $command($request);

            return $command->handle();
        }

        return [
            'message' => 'Unrecognized command. Sorry!',
        ];
    }
}
