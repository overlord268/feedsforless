<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Quotes\QuoteLeadExportService;
use App\Services\Quotes\QuoteLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminQuoteLeadController extends Controller
{
    public function __construct(
        private readonly QuoteLeadService $leadService,
        private readonly QuoteLeadExportService $exportService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter' => [
                'required',
                'string',
                Rule::in([
                    QuoteLeadService::FILTER_UNREGISTERED_WITH_QUOTES,
                    QuoteLeadService::FILTER_WITHOUT_ACCEPTED_QUOTE,
                    QuoteLeadService::FILTER_REGISTERED_NO_QUOTES,
                ]),
            ],
        ]);

        $filter = $validated['filter'];
        $leads = $this->leadService->leadsFor($filter);
        $definition = $this->leadService->definitionFor($filter);

        return response()->json([
            'filter' => $filter,
            'filter_number' => $definition['number'],
            'filter_label' => $definition['label'],
            'filter_description' => $definition['description'],
            'count' => $leads->count(),
            'data' => $leads->map(fn ($lead) => $lead->toArray())->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'filter' => [
                'required',
                'string',
                Rule::in([
                    QuoteLeadService::FILTER_UNREGISTERED_WITH_QUOTES,
                    QuoteLeadService::FILTER_WITHOUT_ACCEPTED_QUOTE,
                    QuoteLeadService::FILTER_REGISTERED_NO_QUOTES,
                ]),
            ],
            'format' => ['nullable', 'string', Rule::in(['xlsx', 'csv'])],
        ]);

        $filter = $validated['filter'];
        $format = $validated['format'] ?? 'xlsx';
        $leads = $this->leadService->leadsFor($filter);

        return $this->exportService->downloadResponse(
            $this->leadService->definitionFor($filter),
            $leads,
            $format,
        );
    }
}
