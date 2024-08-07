<?php

namespace App\Models\Bot;

class Base
{
    /**
     * All commands have parameters.
     *
     * @var array
     */
    protected $params;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        $this->params = $request->all();
    }
}
