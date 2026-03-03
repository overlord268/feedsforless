<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\B2B\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAddressRequest;
use App\Http\Requests\Api\V1\UpdateAddressRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $addresses
        ], 200);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = Address::create([
            'user_id' => $request->user()->id,
            'address_line_1' => $request->validated('address_line_1'),
            'city' => $request->validated('city'),
            'state' => $request->validated('state'),
            'zip_code' => $request->validated('zip_code'),
        ]);

        return response()->json([
            'message' => 'Address created successfully',
            'data' => $address
        ], 201);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $address
        ], 200);
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $address->update($request->validated());

        return response()->json([
            'message' => 'Address updated successfully',
            'data' => $address
        ], 200);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully'
        ], 200);
    }
}