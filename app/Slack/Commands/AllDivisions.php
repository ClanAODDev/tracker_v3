<?php

namespace App\Slack\Commands;

use App\Division;

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
        $this->divisions = Division::lists('name')->toArray();

        return $this->response();
    }

    /**
     * @return array
     */
    public function response()
    {
        return [
            'text' => 'The tracker current supports the following divisions: ',
            'attachments' => [
                [
                    'text' => implode(', ', $this->divisions)
                ],
            ],
        ];
    }
}
