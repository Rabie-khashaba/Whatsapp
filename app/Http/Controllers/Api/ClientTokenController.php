<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ClientTokenController extends Controller
{
    public function generate(Request $request)
    {
        $token = ApiToken::create([
            'user_id' => auth()->id(),
            'token' => Str::random(60)
        ]);

        return response()->json([
            'api_token' => $token->token
        ]);
    }
}
