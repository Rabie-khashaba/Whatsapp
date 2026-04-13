<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function sendMessage(Request $request, WhatsAppService $whatsapp)
    {
        // 1️⃣ Validation
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ]);

        // 2️⃣ جلب التوكين من الهيدر
        $token = $request->bearerToken()
              ?? $request->header('X-API-TOKEN');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token is required'
            ], 401);
        }

        // 3️⃣ إرسال الرسالة باستخدام التوكين
        $result = $whatsapp->sendUsingAccessToken(
            $token,
            $request->phone,
            $request->message
        );

        // 4️⃣ Response موحد
        return response()->json(
            $result,
            $result['success'] ? 200 : 400
        );
    }
}
