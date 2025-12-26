<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseCommand extends Command
{
    protected function logInfo(string $message): void
    {
        $this->info($message);
        Log::info($this->formatLogMessage($message));
    }

    protected function logError(string $message, ?Throwable $exception = null): void
    {
        $this->error($message);
        Log::error($this->formatLogMessage($message), $exception ? ['exception' => $exception->getMessage()] : []);
    }

    protected function logWarning(string $message): void
    {
        $this->warn($message);
        Log::warning($this->formatLogMessage($message));
    }

    protected function formatLogMessage(string $message): string
    {
        return sprintf('[%s] %s', class_basename($this), $message);
    }

    protected function failWithError(string $message, ?Throwable $exception = null): int
    {
        $this->logError($message, $exception);

        return self::FAILURE;
    }

    protected function succeedWithMessage(string $message): int
    {
        $this->logInfo($message);

        return self::SUCCESS;
    }
}
