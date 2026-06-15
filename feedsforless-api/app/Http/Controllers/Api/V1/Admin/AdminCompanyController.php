<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\B2B\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCompanyRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCompanyRequest;
use App\Http\Resources\Api\V1\CompanyResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminCompanyController extends Controller
{
    #[Response(200, 'List of companies.', type: 'array{data: list<CompanyResource>}')]
    public function index(): JsonResponse
    {
        return CompanyResource::dataOnlyCollection(
            Company::orderBy('name', 'asc')->paginate(15)
        )->toResponse(request());
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return (new CompanyResource($company))
            ->additional(['message' => 'Company created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Company $company): CompanyResource
    {
        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());

        return (new CompanyResource($company))
            ->additional(['message' => 'Company updated successfully'])
            ->response();
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}
