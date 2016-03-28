<?php

namespace App\Slack\Commands;

class Help implements Command
{
    private $data;
    private $commands = [
        ['name' => 'help', 'description' => 'Lists all available commands', 'usage' => '/tracker help'],
        ['name' => 'member_sync', 'description' => 'Syncs tracker with forum data. Use only when necessary', 'usage' => '/tracker member_sync'],
        ['name' => 'all_divisions', 'description' => 'Lists all divisions supported by the tracker', 'usage' => '/tracker all_divisions'],
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
            $commandsList .= "{$command['name']}: {$command['description']}. _{$command['usage']}_\n";
        }

        return [
            'text' => "The following commands are currently available. \n\n{$commandsList}",
        ];
    }


}
