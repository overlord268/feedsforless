<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\B2B\Actions\RegisterB2bCustomerAction;
use App\Domains\B2B\DTOs\RegisterB2bCustomerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterB2bCustomerRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(
        RegisterB2bCustomerRequest $request,
        RegisterB2bCustomerAction $action
    ): JsonResponse {
        $dto = new RegisterB2bCustomerDTO(
            companyName: $request->validated('company_name'),
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            jobTitle: $request->validated('job_title'),
            taxClassification: $request->validated('tax_classification'),
            taxRegistrationNumber: $request->validated('tax_registration_number')
        );

        $user = $action->execute($dto);
        $token = $user->createToken('capacitor-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user()->load('roles');
        
        $token = $user->createToken('capacitor-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('roles'))
        ], 200);
    }
}