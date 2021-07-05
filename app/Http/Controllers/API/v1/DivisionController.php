<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Division;
use App\Transformers\DivisionBasicTransformer;
use App\Transformers\DivisionFullTransformer;
use Illuminate\Http\JsonResponse;

class DivisionController extends ApiController
{
    protected $basicTransformer;
    protected $fullTransformer;

    public function __construct(
        DivisionBasicTransformer $basicTransformer,
        DivisionFullTransformer $fullTransformer
    ) {
        $this->basicTransformer = $basicTransformer;
        $this->fullTransformer = $fullTransformer;
    }


    public function index(): JsonResponse
    {
        if ($this->tokenCan('basic:read')) {
            $divisions = Division::get();

            return $this->respond([
                'data' => $this->basicTransformer->transformCollection($divisions->all()),
            ]);
        }

        return $this->setStatusCode(403)
            ->respondWithError("Not authorized to access this endpoint");
    }

    public function show($abbreviation): JsonResponse
    {
        $division = Division::whereAbbreviation($abbreviation)->first();

        if (!$division) {
            return $this->respondNotFound();
        }

        if ($division != auth()->user()->member->division && !$this->tokenCan('clan:read')) {
            return $this->setStatusCode(403)
                ->respondWithError("Not authorized to access this endpoint");
        }

        return $this->respond([
            'data' => $this->fullTransformer->transform($division, true),
        ]);
    }
}
