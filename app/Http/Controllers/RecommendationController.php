<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecommendationRequest;
use App\Http\Requests\UpdateRecommendationRequest;
use App\Models\Member;
use App\Models\Rank;
use App\Models\Recommendation;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($division)
    {
        $recommendations = Recommendation::forCurrentMonth()->get();

        return view('division.recommendations', compact(
            'division',
            'recommendations'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Member $member)
    {
        $recommendableRanks = Rank::where('id', '>=', auth()->user()->member->rank_id)->get();

        return view('division.recommendations', compact(
            'member',
            'recommendableRanks'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreRecommendationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecommendationRequest $request)
    {
        $request->persist();

        $this->showToast('Recommendation submitted successfully');

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Recommendation $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Recommendation $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateRecommendationRequest $request
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecommendationRequest $request, Recommendation $promotion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recommendation $promotion)
    {
        //
    }
}
