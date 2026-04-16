<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Instance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstanceApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API token',
            ], 401);
        }

        $instances = Instance::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (Instance $instance) => $this->transformInstance($instance, $user))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $instances,
        ]);
    }

    public function show(Request $request, string $instance_id): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API token',
            ], 401);
        }

        $instance = Instance::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($instance_id) {
                $query->where('green_instance_id', $instance_id)
                    ->orWhere('id', $instance_id);
            })
            ->first();

        if (!$instance) {
            return response()->json([
                'success' => false,
                'message' => 'Instance not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformInstance($instance, $user),
        ]);
    }

    private function resolveApiUser(Request $request): ?User
    {
        $token = $request->bearerToken() ?? $request->header('X-API-TOKEN') ?? $request->header('token');
        if (!$token) {
            return null;
        }

        $apiToken = ApiToken::query()
            ->where('token', $token)
            ->where('active', true)
            ->first();

        return $apiToken?->user;
    }

    private function transformInstance(Instance $instance, User $user): array
    {
        return [
            'unique_name' => $instance->green_instance_id ?: (string) $instance->id,
            'name' => $instance->name,
            'phone' => $instance->phone_number,
            'status' => $instance->status === 'connected' ? 'WORKING' : strtoupper((string) $instance->status),
            'subscription_type' => 'api',
            'subscription_status' => optional($user->customer)->status ?? 'active',
        ];
    }
}
