<?php

namespace App\Http\Controllers\API\v1;

use App\Models\FallenMember;
use App\Transformers\FallenMemberTransformer;
use Illuminate\Http\JsonResponse;

class FallenMemberController extends ApiController
{
    public function __construct(private readonly FallenMemberTransformer $transformer) {}

    public function index(): JsonResponse
    {
        $fallenMembers = FallenMember::orderBy('display_order')->get();

        return $this->respond([
            'data' => $this->transformer->transformCollection($fallenMembers->all()),
        ]);
    }
}
