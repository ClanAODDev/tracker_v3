<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\File;

class MakeMaintenanceAlert extends BaseCommand
{
    protected const ALERT_FILE = 'maintenance.alert';

    protected $signature = 'tracker:maintenance-alert
                            {--clear : Clear the current maintenance alert}
                            {--set : Set a new maintenance alert}';

    protected $description = 'Manage maintenance alert display';

    public function handle(): int
    {
        $alertPath = base_path(self::ALERT_FILE);

        if ($this->option('clear')) {
            return $this->clearAlert($alertPath);
        }

        if ($this->option('set')) {
            return $this->setAlert($alertPath);
        }

        $this->showCurrentStatus($alertPath);

        return self::SUCCESS;
    }

    protected function clearAlert(string $alertPath): int
    {
        if (! File::exists($alertPath)) {
            $this->info('No alert to clear.');

            return self::SUCCESS;
        }

        File::delete($alertPath);
        $this->info('Alert has been cleared.');

        return self::SUCCESS;
    }

    protected function setAlert(string $alertPath): int
    {
        if (File::exists($alertPath)) {
            if (! $this->confirm('Alert already exists. Replace it?')) {
                return self::SUCCESS;
            }

            File::delete($alertPath);
        }

        $message = $this->ask('What would you like the alert set to? (basic HTML allowed)');

        if (! $message) {
            $this->warn('No message provided. Alert not set.');

            return self::SUCCESS;
        }

        try {
            File::put($alertPath, $message);
            $this->info(sprintf('Alert set to: %s', $message));
        } catch (Exception $exception) {
            return $this->failWithError('Could not set alert file', $exception);
        }

        return self::SUCCESS;
    }

    protected function showCurrentStatus(string $alertPath): void
    {
        if (File::exists($alertPath)) {
            $this->info('Current alert: ' . File::get($alertPath));
        } else {
            $this->info('No maintenance alert is currently set.');
        }

        $this->newLine();
        $this->info('Use --set to create an alert or --clear to remove it.');
    }
}
