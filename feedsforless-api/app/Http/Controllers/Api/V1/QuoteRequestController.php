<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Quotes\Actions\SubmitGuestQuoteRequestAction;
use App\Domains\Quotes\Actions\SubmitQuoteRequestAction;
use App\Domains\Quotes\DTOs\SubmitGuestQuoteRequestDTO;
use App\Domains\Quotes\DTOs\SubmitQuoteRequestDTO;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Quotes\SubmitGuestQuoteRequestRequest;
use App\Http\Requests\Api\V1\Quotes\SubmitQuoteRequestRequest;
use App\Http\Resources\Api\V1\Quotes\QuoteRequestResource;
use App\Mail\GuestQuoteInvitationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Exception;

class QuoteRequestController extends Controller
{
    /**
     * Guest submission: no auth required. Creates quote request with guest email and contact info.
     */
    public function submitGuest(
        SubmitGuestQuoteRequestRequest $request,
        SubmitGuestQuoteRequestAction $action
    ): JsonResponse {
        try {
            $dto = new SubmitGuestQuoteRequestDTO(
                productId: $request->validated('product_id'),
                packagingTypeId: $request->validated('packaging_type_id'),
                quantity: $request->validated('quantity'),
                deliveryZip: $request->validated('delivery_zip'),
                email: $request->validated('email'),
                legalName: $request->validated('legal_name'),
                contactName: $request->validated('contact_name'),
                phone: $request->validated('phone'),
                destinationAddress: $request->validated('destination_address'),
                requiresLiftgate: $request->boolean('requires_liftgate'),
                requiresAppointment: $request->boolean('requires_appointment'),
                taxId: $request->validated('tax_id'),
            );

            $quoteRequest = $action->execute($dto);

            try {
                $registerUrl = rtrim(config('app.frontend_url', config('app.url')), '/') . '/register';
                Mail::to($request->validated('email'))
                    ->send(new GuestQuoteInvitationMail(
                        guestEmail: $request->validated('email'),
                        contactName: $request->validated('contact_name', ''),
                        registerUrl: $registerUrl
                    ));
            } catch (Exception $mailEx) {
                report($mailEx);
            }

            return response()->json([
                'message' => 'Quote request submitted successfully',
                'data' => new QuoteRequestResource($quoteRequest),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $quotes = QuoteRequest::with(['items.product', 'items.packagingType', 'address'])
            ->where('request_by_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return QuoteRequestResource::collection($quotes);
    }

    public function submit(
        SubmitQuoteRequestRequest $request,
        SubmitQuoteRequestAction $action
    ): JsonResponse {
        try {
            $dto = new SubmitQuoteRequestDTO(
                rfqListId: $request->validated('rfq_list_id'),
                userId: $request->user()->id,
                deliveryZip: $request->validated('delivery_zip'),
                requiresLiftgate: $request->boolean('requires_liftgate'),
                requiresAppointment: $request->boolean('requires_appointment'),
                targetAddressId: $request->validated('target_address_id')
            );

            $quoteRequest = $action->execute($dto);

            return response()->json([
                'message' => 'Quote request submitted successfully',
                'data' => new QuoteRequestResource($quoteRequest),
            ], 201);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function accept(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        if ($quoteRequest->request_by_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($quoteRequest->status !== 'quoted') {
            return response()->json(['message' => 'Only quoted requests can be accepted'], 400);
        }

        $quoteRequest->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Quote accepted successfully',
            'data' => new QuoteRequestResource($quoteRequest->load(['items.product', 'items.packagingType'])),
        ]);
    }

    public function reject(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        if ($quoteRequest->request_by_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($quoteRequest->status !== 'quoted') {
            return response()->json(['message' => 'Only quoted requests can be rejected'], 400);
        }

        $quoteRequest->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Quote rejected successfully',
            'data' => new QuoteRequestResource($quoteRequest->load(['items.product', 'items.packagingType'])),
        ]);
    }
}