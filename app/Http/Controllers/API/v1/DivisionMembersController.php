<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Division;
use App\Transformers\DivisionBasicTransformer;
use App\Transformers\MemberBasicTransformer;
use Illuminate\Http\JsonResponse;

class DivisionMembersController extends ApiController
{
    protected DivisionBasicTransformer $divisionTransformer;
    protected MemberBasicTransformer $memberTransformer;

    public function __construct(
        DivisionBasicTransformer $divisionTransformer,
        MemberBasicTransformer $memberTransformer
    ) {
        $this->divisionTransformer = $divisionTransformer;
        $this->memberTransformer = $memberTransformer;
    }

    public function show($abbreviation): JsonResponse
    {
        if (!auth()->user()->tokenCan('basic:read')) {
            return $this->setStatusCode(403)
                ->respondWithError("Not authorized to access this endpoint");
        }

        $division = Division::whereAbbreviation($abbreviation)->first();
        $members = $division->members()->paginate(25);

        if (!$division) {
            return $this->respondNotFound();
        }

        return $this->respond(array_merge($this->paginatorDetails($members), [
            'data' => [
                'division' => $this->divisionTransformer->transform($division),
                'members' => $this->memberTransformer->transformCollection(
                    $members->all()
                ),

            ],
        ]));
    }
}
