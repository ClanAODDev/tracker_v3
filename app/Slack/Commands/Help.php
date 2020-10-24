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
            'name' => 'Search members',
            'description' => 'Search for members in AOD. Can search for up to two names. Can search for a portion of a name. Cannot return more than 10 results at a time.',
            'usage' => '!tracker search:archan,kid_a',
        ],

        [
            'name' => 'Search members by discord username',
            'description' => 'Search for members in AOD via discord tag. Cannot return more than 10 results at a time.',
            'usage' => '!tracker search-discord:sulifex'
        ],

        [
            'name' => 'Search members by TS Unique ID',
            'description' => 'Search for members in AOD via TS unique ID. Cannot return more than 10 results at a time.',
            'usage' => '!tracker search-teamspeak:unique-id-string-here'
        ]
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
                'fields' => collect($this->commands)->map(fn($command) => [
                    'name' => "{$command['name']}: {$command['description']}",
                    'value' => "Ex. {$command['usage']}\r\n\r\n"
                ]),
            ]
        ];
    }
}
