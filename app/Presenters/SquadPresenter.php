<?php

namespace App\Presenters;

use App\Models\Squad;

class SquadPresenter extends Presenter
{
    public function __construct(Squad $squad)
    {
        $this->squad = $squad;
    }
}
