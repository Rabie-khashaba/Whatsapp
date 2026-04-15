<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Exception;

class WhatsAppService
{
    private $maxMessagesPerHour = 1000;
    private $maxMessagesPerDay = 10000;

    public function sendUsingAccessToken(string $token, string $phone, string $message)
    {
        try {
            logger('WhatsAppService called', [
                'token' => substr($token, 0, 10) . '***',
                'phone' => $phone,
            ]);

            // 1. البحث عن الـ instance
            $instance = Instance::with('user.customer')
                ->where('access_token', $token)
                ->where('status', 'connected')
                ->firstOrFail();

            // 1.5. فحص حالة العميل (السماح إذا كانت فترة التجربة نشطة)
            if ($instance->user->customer) {
                $instance->user->customer->updateTrialStatusIfExpired();
            }

            $blockedStatuses = ['cancelled', 'expired', 'pending'];
            if ($instance->user->customer && in_array($instance->user->customer->status, $blockedStatuses) && !$instance->user->customer->hasActiveTrial()) {
                return [
                    'success' => false,
                    'error' => 'Your subscription is not active. Please renew your subscription to continue using this service.'
                ];
            }

            // 2. فحص الـ rate limits
            $this->checkRateLimits($instance->id);

            // 3. إرسال الرسالة
            $response = Http::timeout(10)
                ->retry(3, 100)
                ->post(
                    config('services.baileys.url') . "/api/instance/{$instance->green_instance_id}/send",
                    [
                        'phone' => $phone,
                        'message' => $message
                    ]
                );

            $responseData = $response->json();

            // 4. تسجيل الرسالة
            $this->logMessage($instance->id, $phone, $message, $response->status(), $responseData);

            // 5. تحديث الـ counters
            $this->incrementCounters($instance->id);

            logger('Message sent successfully', [
                'instance_id' => $instance->id,
                'status' => $response->status()
            ]);

            return [
                'success' => true,
                'data' => $responseData,
                'stats' => $this->getInstanceStats($instance->id)
            ];

        } catch (Exception $e) {
            logger('WhatsApp send failed', [
                'error' => $e->getMessage(),
                'phone' => $phone
            ]);

            $this->logMessage(
                $instance->id ?? null,
                $phone,
                $message,
                0,
                ['error' => $e->getMessage()]
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkRateLimits(int $instanceId)
    {
        $hourKey = "whatsapp:rate:hour:{$instanceId}:" . now()->format('YmdH');
        $dayKey = "whatsapp:rate:day:{$instanceId}:" . now()->format('Ymd');

        $hourCount = Cache::get($hourKey, 0);
        $dayCount = Cache::get($dayKey, 0);

        if ($hourCount >= $this->maxMessagesPerHour) {
            throw new Exception('Rate limit exceeded: Maximum messages per hour reached');
        }

        if ($dayCount >= $this->maxMessagesPerDay) {
            throw new Exception('Rate limit exceeded: Maximum messages per day reached');
        }
    }

    private function incrementCounters(int $instanceId)
    {
        $hourKey = "whatsapp:rate:hour:{$instanceId}:" . now()->format('YmdH');
        $dayKey = "whatsapp:rate:day:{$instanceId}:" . now()->format('Ymd');

        Cache::increment($hourKey);
        Cache::put($hourKey, Cache::get($hourKey), now()->addHour());

        Cache::increment($dayKey);
        Cache::put($dayKey, Cache::get($dayKey), now()->addDay());
    }

    private function logMessage($instanceId, $phone, $message, $status, $response)
    {
        MessageLog::create([
            'instance_id' => $instanceId,
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'response' => $response,
            'sent_at' => now()
        ]);
    }

    public function getInstanceStats(int $instanceId)
    {
        $hourKey = "whatsapp:rate:hour:{$instanceId}:" . now()->format('YmdH');
        $dayKey = "whatsapp:rate:day:{$instanceId}:" . now()->format('Ymd');

        return [
            'messages_this_hour' => Cache::get($hourKey, 0),
            'messages_today' => Cache::get($dayKey, 0),
            'hourly_limit' => $this->maxMessagesPerHour,
            'daily_limit' => $this->maxMessagesPerDay,
            'hourly_remaining' => $this->maxMessagesPerHour - Cache::get($hourKey, 0),
            'daily_remaining' => $this->maxMessagesPerDay - Cache::get($dayKey, 0),
        ];
    }

    public function getDetailedStats(int $instanceId, $days = 7)
    {
        $stats = MessageLog::where('instance_id', $instanceId)
            ->where('sent_at', '>=', now()->subDays($days))
            ->selectRaw('
                DATE(sent_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 200 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status != 200 THEN 1 ELSE 0 END) as failed
            ')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return [
            'daily_stats' => $stats,
            'current_limits' => $this->getInstanceStats($instanceId),
            'total_messages' => MessageLog::where('instance_id', $instanceId)->count(),
            'success_rate' => $this->calculateSuccessRate($instanceId)
        ];
    }

    private function calculateSuccessRate(int $instanceId)
    {
        $total = MessageLog::where('instance_id', $instanceId)->count();

        if ($total === 0) return 100;

        $successful = MessageLog::where('instance_id', $instanceId)
            ->where('status', 200)
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
