<?php

namespace App\Http\Controllers;

use App\Repositories\MemberRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    public function __construct(MemberRepository $memberRepository)
    {
        $this->member = $memberRepository;

        $this->middleware(['auth']);
    }

    /**
     * @param $division
     * @param null $month
     * @param null $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($division, $month = null, $year = null)
    {
        try {
            $members = $this->getMemberPromotions($division, $month, $year);
        } catch (\Exception $exception) {
            $members = [];
        }

        $ranks = $members->pluck('rank.abbreviation')->unique();
        $counts = $members->groupBy('rank_id')->each(function ($rank) {
            $rank->count = count($rank);
        })->pluck('count');

        $promotionPeriods = $this->member->promotionPeriods();

        return view('division.promotions', compact(
            'members',
            'division',
            'promotionPeriods',
            'year',
            'month',
            'ranks',
            'counts'
        ));
    }

    /**
     * @param $division
     * @param $month
     * @param $year
     * @return mixed
     */
    private function getMemberPromotions($division, $month, $year)
    {
        $dates = ($month && $year) ? [
            Carbon::parse($month . " {$year}")->startOfMonth(),
            Carbon::parse($month . " {$year}")->endOfMonth()
        ] : [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ];

        $members = $division->members()
            ->with('rank')
            ->whereBetween('last_promoted', $dates)
            ->orderByDesc('rank_id')->get();

        return $members;
    }
}
