<?php

namespace App\Slack\Commands;

use App\Slack\Base;
use App\Slack\Command;

class Help extends Base implements Command
{
    private $content = [];
    private $commands = [
        [
            'name' => 'Help reference',
            'description' => 'Lists all available commands for the tracker integration',
            'usage' => '/tracker help',
        ],

        [
            'name' => 'All supported divisions',
            'description' => 'Lists all divisions supported by the tracker',
            'usage' => '/tracker all_divisions',
        ],

        [
            'name' => 'Search members',
            'description' => 'Search members in divisions supported by tracker',
            'usage' => '/tracker search:guybrush',
        ],
    ];

    /**
     * @return mixed
     */
    public function handle()
    {

        $this->content = collect($this->commands)
            ->each(function ($command) {
                return [
                    'text' => "{$command['name']}: {$command['description']}.\r\n Ex. {$command['usage']}\r\n\r\n"
                ];
            });

        $this->content[] = [
            'text' => 'More commands will be added soon!'
        ];

        return [
            'text' => "The following commands are currently available.",
            'attachments' => $this->content,
        ];
    }
}
