<?php

namespace App\Slack\Commands;

class Help implements Command
{
    private $data;

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
        return [
            'text' => 'Member sync performed successfully!',
        ];
    }


}
