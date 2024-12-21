<?php

namespace App\Console\Commands;

use App\Models\Award;
use App\Models\MemberAward;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportAwardsData extends Command
{
    protected $signature = 'app:import-awards-data';

    protected $description = 'Import process for ClanAOD member individual forum awards';

    protected array $fileMappings = [
        'awards' => [
            'file' => 'app/vb_award.csv',
            'expectedColumns' => [
                'award_id',
                'award_cat_id',
                'award_name',
                'award_desc',
                'award_icon_url',
                'award_img_url',
                'award_displayorder',
                'award_allowrequest',
                'award_active',
                'award_allowrecommend',
                'award_pointvalue',
            ],
            'model' => Award::class,
            'mapping' => [
                'id' => 'award_id',
                'name' => 'award_name',
                'description' => 'award_desc',
                'display_order' => 'award_displayorder',
            ],
        ],
        'memberAwards' => [
            'file' => 'app/vb_award_user.csv',
            'expectedColumns' => [
                'issue_id',
                'award_id',
                'userid',
                'issue_reason',
                'issue_time',
            ],
            'model' => MemberAward::class,
            'mapping' => [
                'award_id' => 'award_id',
                'member_id' => 'userid',
                'reason' => 'issue_reason',
                'created_at' => 'issue_time',
                'updated_at' => 'issue_time',
            ],
        ],
    ];

    public function handle()
    {
        $this->info('Starting awards import...');
        foreach ($this->fileMappings as $type => $config) {
            $this->processFile($type, $config);
        }
    }

    protected function processFile(string $type, array $config)
    {
        $filePath = storage_path($config['file']);
        $expectedColumns = $config['expectedColumns'];
        $model = $config['model'];
        $mapping = $config['mapping'];

        if (! file_exists($filePath)) {
            $this->error("Missing required source data - `{$filePath}`");

            return Command::FAILURE;
        }

        $this->info("Validating {$type} file...");

        if (! ($handle = fopen($filePath, 'r'))) {
            $this->error("Failed to open the file: `{$filePath}`");

            return Command::FAILURE;
        }

        $header = fgetcsv($handle, null, ',');
        if (! $this->validateHeader($header, $expectedColumns)) {
            $this->error("Invalid CSV format for {$type}. Expected columns: " . implode(', ', $expectedColumns));
            fclose($handle);

            return Command::FAILURE;
        }

        $this->info("CSV format is valid for {$type}. Processing data...");
        $rowCount = $this->processRows($handle, $header, $model, $mapping);
        fclose($handle);

        $this->info("Successfully imported {$rowCount} {$type} records.");

        return Command::SUCCESS;
    }

    protected function processRows($handle, array $header, string $model, array $mapping): int
    {
        $headerMap = array_flip($header);
        $rowCount = 0;

        while (($row = fgetcsv($handle, null, ',')) !== false) {
            try {
                $data = [];
                foreach ($mapping as $field => $column) {
                    $data[$field] = $this->transformValue($field, $row[$headerMap[$column]]);
                }

                $model::create($data);
                $rowCount++;
            } catch (\Exception $e) {
                $this->error('Error processing row: ' . implode(',', $row));
                $this->error($e->getMessage());
            }
        }

        return $rowCount;
    }

    protected function transformValue(string $field, $value)
    {
        if (in_array($field, ['created_at', 'updated_at']) && is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        return $value;
    }

    protected function validateHeader(array $columns, array $expectedColumns): bool
    {
        return $columns === $expectedColumns;
    }
}
