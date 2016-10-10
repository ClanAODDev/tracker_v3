<?php

namespace App\Slack\Commands;

use App\Division;
use App\Slack\Command;

class AllDivisions implements Command
{
    private $data;
    private $divisions;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function handle()
    {
        $this->divisions = Division::pluck('name')->toArray();

        return [

            'text' => 'The tracker currently supports the following divisions: ',

            'attachments' => [
                [
                    'text' => implode(', ', $this->divisions),
                ],

            ],
        ];
    }
}
