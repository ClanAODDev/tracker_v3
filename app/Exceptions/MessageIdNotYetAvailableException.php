<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class MessageIdNotYetAvailableException extends RuntimeException implements ShouldntReport {}
