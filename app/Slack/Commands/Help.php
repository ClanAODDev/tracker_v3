<?php

namespace App\Slack\Commands;

use App\Slack\Base;
use App\Slack\Command;

class Help extends Base implements Command
{
    private $commands = [
        [
            'name' => 'Help reference',
            'description' => 'Lists all available commands for the tracker integration',
            'usage' => '!tracker help',
        ],

        [
            'name' => 'All supported divisions',
            'description' => 'Lists all divisions supported by the tracker',
            'usage' => '!tracker all_divisions',
        ],

        [
            'name' => 'Search members',
            'description' => 'Search for members in AOD. Can search for up to two names. Can search for a portion of a name. Cannot return more than 10 results at a time.',
            'usage' => '!tracker search:archan,kid_a',
        ],
    ];

    /**
     * @return mixed
     */
    public function handle()
    {
        return [
            'embed' => [
                'title' => 'The following commands are currently available.',
                'author' => [
                    'name' => 'AOD Tracker'
                ],
                'color' => 10181046,
                'fields' => collect($this->commands)->map(function ($command) {
                    return [
                        'name' => "{$command['name']}: {$command['description']}",
                        'value' => "Ex. {$command['usage']}\r\n\r\n"
                    ];
                }),
            ]
        ];
    }
}
