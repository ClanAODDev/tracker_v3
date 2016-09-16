<?php

namespace App\Presenters;

use App\Division;

class DivisionPresenter extends Presenter
{
    /**
     * @var Division
     */
    protected $division;

    public function __construct(Division $division)
    {
        $this->division = $division;
    }
}
