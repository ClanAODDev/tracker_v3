<?php

namespace App\Data;

readonly class DivisionComparisonData
{
    public function __construct(
        public int $avgTenureDays,
        public float $avgTenureYears,
        public int $avgVoiceDays,
        public int $tenurePercentile,
        public int $activityPercentile,
        public bool $tenureBetter,
        public bool $activityBetter,
    ) {}
}
