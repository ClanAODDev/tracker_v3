<?php

namespace App\Exceptions;

use RuntimeException;

class TenureReconciliationInProgress extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tenure awards are already being reconciled. Try again in a few minutes.');
    }
}
