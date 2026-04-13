<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\WhatsAppService;
use Exception;

class OtpServices
{
    private int $otpExpiryMinutes = 5;
    private int $maxOtpAttempts = 5;
    private int $otpLockoutMinutes = 15;

    /**
     * Generate and send OTP
     *
     * @param string $phone
     * @return int OTP code generated
     */
    public function generateAndSend(string $phone): int
    {
        // 1. توليد OTP
        $code = rand(100000, 999999);

        // 2. حفظ OTP في قاعدة البيانات
        Otp::updateOrCreate(
            ['phone' => $phone],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes($this->otpExpiryMinutes),
                'attempts' => 0
            ]
        );

        Log::info('OTP saved to database', ['phone' => $phone, 'code' => $code]);

        // 3. حفظ session
        session([
            'phone' => $phone,
            'otp_sent_at' => now(),
            'debug_code' => $code
        ]);

        // 4. إرسال WhatsApp
        try {
            $response = app(WhatsAppService::class)->sendUsingAccessToken(
                'xmVM7AtTagzqrfp3SGnbv72FimhSaNo3cArCOKm6HBrmQ6q7psXpw33qKrw8', // ضع توكن WhatsApp هنا
                $phone,
                "كود التحقق: {$code}\nصالح لمدة {$this->otpExpiryMinutes} دقائق"
            );

            Log::info('WhatsApp response', [
                'phone' => $phone,
                'success' => $response['success'] ?? false,
                'error' => $response['error'] ?? null
            ]);

        } catch (Exception $e) {
            Log::error('WhatsApp send failed', ['error' => $e->getMessage()]);
        }

        // 5. تسجيل محاولة OTP
        $this->incrementOtpRequestCount($phone);

        return $code;
    }

    /**
     * Verify OTP code
     *
     * @param string $phone
     * @param string $code
     * @return bool
     * @throws \Exception
     */
    public function verify(string $phone, string $code): bool
    {
        $otp = Otp::where('phone', $phone)->first();

        if (!$otp) {
            throw new \Exception('لا يوجد كود. اطلب كود جديد');
        }

        if ($otp->expires_at < now()) {
            $otp->delete();
            throw new \Exception('الكود منتهي. اطلب كود جديد');
        }

        if ($otp->attempts >= $this->maxOtpAttempts) {
            $otp->delete();
            throw new \Exception('تجاوزت عدد المحاولات');
        }

        if ($otp->code != $code) {
            $otp->increment('attempts');
            $remaining = $this->maxOtpAttempts - $otp->attempts;
            throw new \Exception("كود خاطئ. {$remaining} محاولات متبقية");
        }

        // OTP صحيح، حذف الكود
        $otp->delete();
        $this->clearOtpRequestCount($phone);
        session()->forget(['phone', 'otp_sent_at', 'debug_code']);

        return true;
    }

    /**
     * Check if too many OTP requests
     */
    public function hasTooManyOtpRequests(string $phone): bool
    {
        $key = 'otp_requests:' . $phone;
        return Cache::get($key, 0) >= 3;
    }

    /**
     * Increment OTP request count
     */
    public function incrementOtpRequestCount(string $phone): void
    {
        $key = 'otp_requests:' . $phone;
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, now()->addMinutes($this->otpLockoutMinutes));
    }

    /**
     * Clear OTP request count
     */
    public function clearOtpRequestCount(string $phone): void
    {
        Cache::forget('otp_requests:' . $phone);
    }
}
