<?php

namespace App\Http\Controllers\API\v1;

use App\Division;
use App\Transformers\DivisionTransformer;

class DivisionController extends ApiController
{
    /**
     * @var DivisionTransformer
     */
    protected $divisionTransformer;

    /**
     * DivisionController constructor.
     * @param DivisionTransformer $divisionTransformer
     */
    public function __construct(DivisionTransformer $divisionTransformer)
    {
        $this->divisionTransformer = $divisionTransformer;

        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $divisions = Division::whereActive(true)->with('members')->get();

        return $this->respond([
            'data' => $this->divisionTransformer->transformCollection($divisions->all())
        ]);
    }

    public function show($abbreviation)
    {
        $division = Division::whereAbbreviation($abbreviation)->first();

        if ( ! $division) {
            return $this->respondNotFound('Division could not be found');
        }

        return $this->respond([
            'data' => $this->divisionTransformer->transform($division)
        ]);
    }
}