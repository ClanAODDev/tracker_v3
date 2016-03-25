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
        return Division::all();
    }

}
