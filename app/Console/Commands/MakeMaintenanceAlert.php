<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeMaintenanceAlert extends Command
{
    private const MAINTENANCE_ALERT = 'maintenance.alert';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:alert {--clear }
                                              {--set }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show a maintenance alert (done before a maintenance period)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('clear')) {
            if (! file_exists(base_path(self::MAINTENANCE_ALERT))) {
                $this->info('No alert to clear!');

                return $this::SUCCESS;
            }

            $this->info('Alert has been cleared!');
            unlink(base_path(self::MAINTENANCE_ALERT));
        }

        if ($this->option('set')) {
            if (file_exists(base_path(self::MAINTENANCE_ALERT))) {
                if (! $this->confirm('Alert already exists! Replace it?')) {
                    return $this::SUCCESS;
                }

                unlink(base_path(self::MAINTENANCE_ALERT));
            }

            $message = $this->ask('What would you like the alert set to? (basic HTML allowed)');

            try {
                $alert = fopen(base_path(self::MAINTENANCE_ALERT), 'w');
                fwrite($alert, $message);
                fclose($alert);
            } catch (\Exception $exception) {
                $this->error('Could not set alert file! - '.$exception->getMessage());
            }

            $this->info(sprintf('Alert file set to: %s', $message));
        }

        return $this::SUCCESS;
    }
}
