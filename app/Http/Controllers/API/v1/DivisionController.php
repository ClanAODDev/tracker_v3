<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Division;
use App\Transformers\DivisionBasicTransformer;
use App\Transformers\MemberBasicTransformer;
use Illuminate\Http\JsonResponse;

class DivisionController extends ApiController
{
    private $divisionTransformer;

    private $memberTransformer;

    public function __construct(
        DivisionBasicTransformer $divisionTransformer,
        MemberBasicTransformer $memberTransformer
    ) {
        $this->divisionTransformer = $divisionTransformer;
        $this->memberTransformer = $memberTransformer;
    }

    public function index(): JsonResponse
    {
        if ($this->tokenCan('basic:read')) {
            $divisions = Division::active()
                ->withoutFloaters()
                ->shuttingDown(false)
                ->get();

            return $this->respond([
                'data' => $this->divisionTransformer->transformCollection($divisions->all()),
            ]);
        }

        return $this->setStatusCode(403)
            ->respondWithError('Not authorized to access this endpoint');
    }

    public function show($slug): JsonResponse
    {
        $division = Division::whereSlug($slug)
            ->active()
            ->firstOrFail();

        if ($division !== auth()->user()->member->division && !$this->tokenCan('clan:read')) {
            return $this->setStatusCode(403)
                ->respondWithError('Not authorized to access this endpoint');
        }

        $members = $division->members()->paginate(25);

        return $this->respond(array_merge(
            $this->paginatorDetails($members),
            [
                'data' => [
                    'division' => $this->divisionTransformer->transform($division),
                    'members' => $this->memberTransformer->transformCollection(
                        $members->all()
                    ),
                ],
            ]
        ));
    }
}
