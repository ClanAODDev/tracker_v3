<?php

namespace App\Models\Bot\Reports;

use App\Models\Bot\Report;
use Illuminate\Support\Facades\DB;

class SgtTraining implements Report
{
    public function handle(): string
    {
        $results = DB::select('
            SELECT
                m.name AS ssgt_name,
                COUNT(t.clan_id) AS trainings_completed
            FROM members m
            LEFT JOIN members t ON t.last_trained_by = m.clan_id
                AND t.last_trained_at IS NOT NULL
            WHERE m.rank = 10
            AND m.division_id != 0
            GROUP BY m.clan_id, m.name
            ORDER BY trainings_completed DESC
        ');

        if (empty($results)) {
            return 'No SSgts found.';
        }

        return $this->buildTable($results);
    }

    private function buildTable(array $rows): string
    {
        $columns = ['ssgt_name' => 'SSgt', 'trainings_completed' => 'Trainings'];

        $widths = array_map('strlen', $columns);

        foreach ($rows as $row) {
            foreach (array_keys($columns) as $key) {
                $widths[$key] = max($widths[$key], strlen((string) $row->$key));
            }
        }

        $separator = '+' . implode('+', array_map(fn ($w) => str_repeat('-', $w), $widths)) . '+';
        $header    = '|' . implode('|', array_map(fn ($key, $label) => str_pad($label, $widths[$key]), array_keys($columns), $columns)) . '|';

        $lines = [$separator, $header, $separator];

        foreach ($rows as $row) {
            $lines[] = '|' . implode('|', array_map(
                fn ($key) => str_pad((string) $row->$key, $widths[$key]),
                array_keys($columns)
            )) . '|';
        }

        $lines[] = $separator;

        return implode("\n", $lines);
    }
}
