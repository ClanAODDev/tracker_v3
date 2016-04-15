<?php

namespace App\Slack\Commands;

class Help extends Base implements Command
{
    private $data;
    private $content;
    private $commands = [
        [
            'name' => 'help',
            'description' => 'Lists all available commands',
            'usage' => '/tracker help',
        ],

        [
            'name' => 'member_sync',
            'description' => 'Syncs tracker with forum data. Use only when necessary',
            'usage' => '/tracker member_sync',
        ],

        [
            'name' => 'all_divisions',
            'description' => 'Lists all divisions supported by the tracker',
            'usage' => '/tracker all_divisions',
        ],

        [
            'name' => 'search',
            'description' => 'Search members in divisions supported by tracker',
            'usage' => '/tracker search:playernamehere',
        ],
    ];

    /**
     * MemberSync constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {

        foreach ($this->commands as $command) {
            $this->content .= "*{$command['name']}*: {$command['description']}.\r\n Ex. {$command['usage']}\r\n\r\n";
        }

        return $this->response();
    }


    /**
     * Response should either provide a JSON response right away
     * or POST to the response_url if the request takes
     * longer than 300ms
     *
     * @return mixed
     */
    public function response()
    {
        return [
            'text' => "The following commands are currently available.",
            'attachments' => [
                [
                    'text' => $this->content,
                ],
            ],
        ];
    }
}
