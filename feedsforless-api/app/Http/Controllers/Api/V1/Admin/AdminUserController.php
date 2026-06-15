<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\B2B\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreUserRequest;
use App\Http\Requests\Api\V1\Admin\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    #[Response(200, 'List of users.', type: 'array{data: list<UserResource>}')]
    public function index(): JsonResponse
    {
        return UserResource::dataOnlyCollection(
            User::with('roles')->orderBy('created_at', 'desc')->paginate(15)
        )->toResponse(request());
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return (new UserResource($user->load('roles')))
            ->additional(['message' => 'User created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return (new UserResource($user->load('roles')))
            ->additional(['message' => 'User updated successfully'])
            ->response();
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,customer'],
        ]);
        $user->syncRoles([$validated['role']]);

        return (new UserResource($user->load('roles')))
            ->additional(['message' => 'Role updated successfully'])
            ->response();
    }
}
