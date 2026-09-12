<?php

namespace App\Http\Controllers\Division;

use App\Data\DivisionCensusReportData;
use App\Data\DivisionPromotionsReportData;
use App\Data\DivisionRetentionReportData;
use App\Data\DivisionTransferReportData;
use App\Data\DivisionVoiceReportData;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Repositories\DivisionRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class ReportController extends Controller
{
    public function __construct(private DivisionRepository $division) {}

    public function retentionReport(Division $division): Response
    {
        return Inertia::render('division/reports/retention', DivisionRetentionReportData::for($division, $this->division)->toArray());
    }

    public function voiceReport(Division $division): Response
    {
        return Inertia::render('division/reports/voice', DivisionVoiceReportData::for($division)->toArray());
    }

    public function censusReport(Division $division): Response
    {
        return Inertia::render('division/reports/census', DivisionCensusReportData::for($division)->toArray());
    }

    public function promotionsReport(
        Request $request,
        Division $division,
        ?int $month = null,
        ?int $year = null
    ): Response {
        if ($period = $request->query('period')) {
            [$year, $month] = explode('-', $period);
            $year           = (int) $year;
            $month          = (int) $month;
        } else {
            $month = $month ? (int) $month : null;
            $year  = $year ? (int) $year : null;
        }

        return Inertia::render('division/reports/promotions', DivisionPromotionsReportData::for($division, $month, $year)->toArray());
    }

    public function transferReport(Division $division): Response
    {
        return Inertia::render('division/reports/transfers', DivisionTransferReportData::for($division)->toArray());
    }
}
