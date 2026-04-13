<?php

// app/Http/Controllers/WebhookController.php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('WhatsApp Webhook Received', $data);

        // التأكد من وجود البيانات المطلوبة
        if (isset($data['instanceId']) && isset($data['messages'])) {
            $instance = \App\Models\Instance::where('green_instance_id', $data['instanceId'])->first();

            if ($instance) {
                foreach ($data['messages'] as $msg) {
                    // تجنب تكرار الرسائل المخزنة بالفعل
                    $exists = \App\Models\MessageLog::where('message_id', $msg['id'])->exists();

                    if (!$exists) {
                        \App\Models\MessageLog::create([
                            'instance_id' => $instance->id,
                            'phone' => str_replace('@s.whatsapp.net', '', $msg['phone']),
                            'message' => $msg['text'] ?? '',
                            'status' => 200, // رسالة ناجحة قادمة من الموبايل
                            'message_id' => $msg['id'],
                            'type' => 'text',
                            'sent_at' => now(),
                        ]);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
