<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\API\UpdateDivision;
use App\Models\Division;
use App\Transformers\DivisionBasicTransformer;
use App\Transformers\MemberBasicTransformer;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function update($division, UpdateDivision $request): JsonResponse
    {
        $division = \App\Models\Division::where('slug', $division)->first();

        if (!$division) {
            return $this->setStatusCode(404)->respondWithError('Invalid division provided');
        }

        $request->persist($division);

        return $this->respond([])->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function index(): JsonResponse
    {
        $divisions = Division::active()
            ->withoutFloaters()
            ->shuttingDown(false)
            ->get();

        return $this->respond([
            'data' => $this->divisionTransformer->transformCollection($divisions->all()),
        ]);
    }

    public function show($slug): JsonResponse
    {
        $division = Division::where('slug', strtolower($slug))
            ->active()
            ->first();

        if (!$division) {
            return $this->setStatusCode(404)->respondWithError('Invalid division provided');
        }

        if (request()->user()->tokenCan('division:read-advanced') && request()->has('include_members')) {
            $members = $division->members()->paginate(25);

            return $this->respond(array_merge($this->paginatorDetails($members),
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

        return $this->respond([
            'data' => [
                'division' => $this->divisionTransformer->transform($division),
            ]
        ]);
    }
}
