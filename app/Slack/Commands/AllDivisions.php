<?php

namespace App\Slack\Commands;

use App\Division;

class AllDivisions implements Command
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $divisions = Division::lists('name')->toArray();

        return [
            'text' => 'The tracker current supports the following divisions: ',
            'attachments' => [
                [
                    'text' => implode(', ', $divisions)
                ],
            ],
        ];
    }

}
