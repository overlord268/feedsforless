<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Quotes\Actions\AddProductToRfqListAction;
use App\Domains\Quotes\DTOs\AddProductToRfqListDTO;
use App\Domains\Quotes\Models\RfqList;
use App\Domains\Quotes\Models\RfqListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Quotes\AddProductToRfqListRequest;
use App\Http\Resources\Api\V1\Quotes\RfqListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfqListController extends Controller
{
    public function addItem(
        AddProductToRfqListRequest $request,
        AddProductToRfqListAction $action
    ): JsonResponse {
        $dto = new AddProductToRfqListDTO(
            productId: $request->validated('product_id'),
            packagingTypeId: $request->validated('packaging_type_id'),
            quantity: $request->validated('quantity'),
            userId: $request->user()?->id,
            sessionId: $request->validated('session_id')
        );

        $rfqList = $action->execute($dto);

        return response()->json([
            'message' => 'Product added to RFQ list successfully',
            'data' => new RfqListResource($rfqList),
        ], 200);
    }

    public function show(Request $request): JsonResponse
    {
        $query = RfqList::with(['items.product', 'items.packagingType'])
            ->where('status', 'active');

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('session_id', $request->query('session_id'));
        }

        $rfqList = $query->first();

        if (!$rfqList) {
            return response()->json([
                'data' => null
            ], 200);
        }

        return response()->json([
            'data' => new RfqListResource($rfqList)
        ], 200);
    }

    public function removeItem(int $itemId): JsonResponse
    {
        $item = RfqListItem::findOrFail($itemId);
        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ], 200);
    }
}