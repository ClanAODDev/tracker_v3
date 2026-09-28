<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Throwable;

class SteamApiException extends Exception
{
    public static function missingKey(): self
    {
        return new self('Steam API key is not configured.');
    }

    public static function fromResponse(Response $response): self
    {
        return new self("Steam API returned HTTP {$response->status()}.", $response->status());
    }

    public static function unreachable(Throwable $previous): self
    {
        return new self('Steam API could not be reached.', previous: $previous);
    }
}
