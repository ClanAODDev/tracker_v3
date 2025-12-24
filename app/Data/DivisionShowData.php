<?php

namespace App\Data;

use App\Models\Division;
use Illuminate\Support\Collection;

readonly class DivisionShowData
{
    public function __construct(
        public Division $division,
        public DivisionStatsData $stats,
        public CensusChartData $chartData,
        public Collection $platoons,
        public Collection $divisionLeaders,
        public Collection $generalSergeants,
        public Collection $divisionAnniversaries,
        public ?object $previousCensus,
        public int $outstandingInactives,
        public int $outstandingAwardRequests,
    ) {}

    public function toArray(): array
    {
        return [
            'division' => $this->division,
            'stats' => $this->stats,
            'chartData' => $this->chartData->toArray(),
            'platoons' => $this->platoons,
            'divisionLeaders' => $this->divisionLeaders,
            'generalSergeants' => $this->generalSergeants,
            'divisionAnniversaries' => $this->divisionAnniversaries,
            'previousCensus' => $this->previousCensus,
            'outstandingInactives' => $this->outstandingInactives,
            'outstandingAwardRequests' => $this->outstandingAwardRequests,
        ];
    }
}
