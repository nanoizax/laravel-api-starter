<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Users", description="User management endpoints")
 */
class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="List users (admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="per_page", in="query", schema={"type": "integer", "default": 20}),
     *     @OA\Parameter(name="role", in="query", schema={"type": "string", "enum": {"admin", "user"}}),
     *     @OA\Parameter(name="is_active", in="query", schema={"type": "boolean"}),
     *     @OA\Response(response=200, description="Paginated user list")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginate(
            $request->integer('per_page', 20),
            $request->only(['role', 'is_active']),
        );

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get user by ID (admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, schema={"type": "string", "format": "uuid"}),
     *     @OA\Response(response=200, description="User data"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findOrFail($id);

        return response()->json(['success' => true, 'data' => new UserResource($user)]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update user",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, schema={"type": "string"}),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated user")
     * )
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->findOrFail($id);
        $user = $this->userService->update($user, $request->validated());

        return response()->json(['success' => true, 'data' => new UserResource($user)]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete user (admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, schema={"type": "string"}),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $user = $this->userService->findOrFail($id);
        $this->userService->delete($user);

        return response()->json(['success' => true, 'message' => 'User deleted']);
    }
}
