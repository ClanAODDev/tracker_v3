<?php

namespace App\Exceptions;

use App\Solutions\SeederMissingSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

class SeederMissingException extends Exception implements ProvidesSolution
{
    /**
     * @return SeederMissingSolution|Solution
     */
    public function getSolution(): Solution
    {
        return new SeederMissingSolution();
    }
}
