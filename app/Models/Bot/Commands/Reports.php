<?php

namespace App\Models\Bot\Commands;

use App\Models\Bot\Base;
use App\Models\Bot\Command;
use Illuminate\Support\Str;

class Reports extends Base implements Command
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function handle()
    {
        $validated = $this->request->validate([
            'value' => 'required|string',
        ]);

        $class = sprintf('App\Models\Bot\Reports\%s', Str::studly($validated['value']));

        if (! class_exists($class)) {
            return ['message' => "Report '{$validated['name']}' not found."];
        }

        $result = (new $class)->handle();

        return ['message' => "```\n{$result}\n```"];
    }
}
