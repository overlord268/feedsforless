<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\DeleteFflSkuMapRequest;
use App\Http\Requests\Api\V1\Admin\StoreFflSkuGradeRequest;
use App\Http\Requests\Api\V1\Admin\UpdateFflSkuGradeRequest;
use App\Models\FflSkuGrade;
use App\Services\Catalog\FflSkuGradeService;
use Illuminate\Http\JsonResponse;

class AdminFflSkuConfigController extends Controller
{
    public function __construct(
        private readonly FflSkuGradeService $gradeService
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->gradeService->snapshot(),
        ]);
    }

    public function audits(): JsonResponse
    {
        return response()->json([
            'data' => $this->gradeService->recentAudits(),
        ]);
    }

    public function storeGrade(StoreFflSkuGradeRequest $request): JsonResponse
    {
        $grade = $this->gradeService->create(
            $request->safe()->only(['grade_spec', 'sku_code']),
            $request->user(),
            $request->ip()
        );

        return response()->json([
            'message' => 'Grade registered.',
            'data' => $grade,
        ], 201);
    }

    public function updateGrade(UpdateFflSkuGradeRequest $request, FflSkuGrade $grade): JsonResponse
    {
        $grade = $this->gradeService->update(
            $grade,
            $request->safe()->only(['grade_spec', 'sku_code']),
            $request->user(),
            $request->ip()
        );

        return response()->json([
            'message' => 'Grade updated.',
            'data' => $grade,
        ]);
    }

    public function destroyGrade(DeleteFflSkuMapRequest $request, FflSkuGrade $grade): JsonResponse
    {
        $this->gradeService->delete($grade, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Grade deleted.',
        ]);
    }
}
