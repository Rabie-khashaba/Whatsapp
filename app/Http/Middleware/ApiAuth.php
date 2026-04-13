<?php

namespace App\Http\Middleware;

use App\Models\Instance;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-TOKEN');

        if (!$token) {
            return response()->json([
                'message' => 'API token missing'
            ], 401);
        }

        $instance = Instance::where('access_token', $token)
            ->where('status', 'connected')
            ->first();

        if (!$instance) {
            return response()->json([
                'message' => 'Invalid or inactive token'
            ], 401);
        }

        // Inject instance
        $request->merge([
            'instance' => $instance
        ]);

        return $next($request);
    }
}
