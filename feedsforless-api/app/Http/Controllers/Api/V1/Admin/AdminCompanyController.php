<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\B2B\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCompanyRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCompanyRequest;
use Illuminate\Http\JsonResponse;

class AdminCompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::orderBy('name', 'asc')->paginate(15);

        return response()->json([
            'data' => $companies
        ], 200);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return response()->json([
            'message' => 'Company created successfully',
            'data' => $company
        ], 201);
    }

    public function show(Company $company): JsonResponse
    {
        return response()->json([
            'data' => $company
        ], 200);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());

        return response()->json([
            'message' => 'Company updated successfully',
            'data' => $company
        ], 200);
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully'
        ], 200);
    }
}