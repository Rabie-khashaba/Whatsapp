<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsAppService;
use App\Helpers\PhoneHelper;
use Exception;
use Illuminate\Support\Facades\Log;

class OtpLoginController extends Controller
{
    private $maxOtpAttempts = 5;
    private $otpLockoutMinutes = 15;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'password' => 'required|string|min:6',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف يجب أن يكون 10-11 رقم',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        ]);

        $credentials = [
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'phone' => 'بيانات الدخول غير صحيحة. تحقق من رقم الهاتف وكلمة المرور.',
        ])->withInput();
    }

    public function sendOtp(Request $request)
    {
        // 1. Log كل حاجة من البداية
        Log::info('=== OTP SEND STARTED ===', [
            'phone_input' => $request->phone,
            'all_input' => $request->all()
        ]);

        try {
            // 2. التحقق من المدخلات
            $validated = $request->validate([
                'phone' => 'required|regex:/^[0-9]{10,11}$/'
            ], [
                'phone.required' => 'رقم الهاتف مطلوب',
                'phone.regex' => 'رقم الهاتف يجب أن يكون 10-11 رقم'
            ]);

            Log::info('Validation passed', $validated);

            // 3. تطبيع الرقم
            $phone = PhoneHelper::normalizeEgyptPhone($request->phone);
            Log::info('Phone normalized', ['phone' => $phone]);

            // 4. التحقق من وجود المستخدم
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                Log::warning('User not found during login', ['phone' => $request->phone]);
                return back()->withErrors([
                    'phone' => 'رقم الهاتف غير مسجل لدينا. يرجى إنشاء حساب أولاً.'
                ])->withInput();
            }



            // 5. فحص Rate Limiting
            // if ($this->hasTooManyOtpRequests($phone)) {
            //     Log::warning('Rate limit exceeded', ['phone' => $phone]);
            //     return back()->withErrors([
            //         'phone' => "تجاوزت الحد المسموح. انتظر {$this->otpLockoutMinutes} دقيقة"
            //     ])->withInput();
            // }

            // 6. توليد OTP
            $code = rand(100000, 999999);
            Log::info('OTP generated', ['code' => $code]);

            // 7. حفظ OTP في Database
            Otp::updateOrCreate(
                ['phone' => $request->phone],
                [
                    'code' => $code,
                    'expires_at' => Carbon::now()->addMinutes(5),
                    'attempts' => 0
                ]
            );
            Log::info('OTP saved to database');

            // 8. حفظ Session أولاً (الأهم!)
            session([
                'phone' => $request->phone,
                'otp_sent_at' => now(),
                'debug_code' => $code // للتجربة فقط
            ]);
            Log::info('Session saved', [
                'phone' => session('phone'),
                'session_id' => session()->getId()
            ]);

            // 9. محاولة إرسال WhatsApp (اختياري)
            $whatsappSent = false;
            $whatsappError = null;

            try {
                Log::info('Attempting WhatsApp send...');

                $whatsappResponse = app(WhatsAppService::class)
                    ->sendUsingAccessToken(
                        config('services.whatsapp.access_token'),
                        $phone,
                        "كود التحقق: {$code}\nصالح لمدة 5 دقائق"
                    );

                $whatsappSent = $whatsappResponse['success'] ?? false;
                $whatsappError = $whatsappResponse['error'] ?? null;

                Log::info('WhatsApp response', [
                    'success' => $whatsappSent,
                    'error' => $whatsappError
                ]);

            } catch (Exception $e) {
                $whatsappError = $e->getMessage();
                Log::error('WhatsApp exception', [
                    'error' => $whatsappError,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // 10. تسجيل المحاولة
            $this->incrementOtpRequestCount($phone);

            // 11. التوجيه لصفحة OTP (في كل الأحوال!)
            Log::info('Redirecting to verify.otp page');

            if ($whatsappSent) {
                return redirect()->route('verify.otp.login')
                    ->with('success', 'تم إرسال الكود إلى WhatsApp ✅');
            } else {
                return redirect()->route('verify.otp.login')
                    ->with('warning', "الكود: {$code} (لم يُرسل عبر WhatsApp)");
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (Exception $e) {
            Log::error('=== FATAL ERROR ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'phone' => 'خطأ: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function showOtpForm()
    {
        $phone = session('phone');

        Log::info('OTP form accessed', [
            'phone' => $phone,
            'session_id' => session()->getId()
        ]);

        if (!$phone) {
            Log::warning('No phone in session, redirecting to login');
            return redirect()->route('login')
                ->withErrors(['phone' => 'الجلسة منتهية. سجل دخول مرة أخرى']);
        }

        return view('auth.login-otp', [
            'phone' => $phone,
            'debug_code' => session('debug_code') // للتجربة
        ]);
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|digits:6'
            ], [
                'otp.required' => 'الكود مطلوب',
                'otp.digits' => 'الكود يجب أن يكون 6 أرقام'
            ]);

            $phone = session('phone');
            //return $phone;

            if (!$phone) {
                return redirect()->route('login')
                    ->withErrors(['phone' => 'الجلسة منتهية']);
            }

            $otp = Otp::where('phone', $phone)->first();

            if (!$otp) {
                return back()->withErrors([
                    'otp' => 'لا يوجد كود. اطلب كود جديد'
                ]);
            }

            if ($otp->expires_at < now()) {
                $otp->delete();
                return back()->withErrors([
                    'otp' => 'الكود منتهي. اطلب كود جديد'
                ]);
            }

            if ($otp->attempts >= $this->maxOtpAttempts) {
                $otp->delete();
                return back()->withErrors([
                    'otp' => 'تجاوزت عدد المحاولات'
                ]);
            }

            if ($otp->code != $request->otp) {
                $otp->increment('attempts');
                $remaining = $this->maxOtpAttempts - $otp->attempts;

                return back()->withErrors([
                    'otp' => "كود خاطئ. {$remaining} محاولات متبقية"
                ])->withInput();
            }

            $user = User::where('phone', $phone)->first();

            //return  $user;

            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['phone' => 'المستخدم غير موجود']);
            }

            Auth::login($user);
            Log::info('Auth check after login', ['check' => Auth::check()]);

            $otp->delete();
            session()->forget(['phone', 'otp_sent_at', 'debug_code']);
            $this->clearOtpRequestCount($phone);

            Log::info('User logged in', ['user_id' => $user->id]);

            return redirect()->route('dashboard')
                ->with('success', 'تم تسجيل الدخول بنجاح');

        } catch (Exception $e) {
            Log::error('Verify error', ['error' => $e->getMessage()]);
            return back()->withErrors(['otp' => 'حدث خطأ']);
        }
    }

    public function resendOtp()
    {
        try {
            $phone = session('phone');

            if (!$phone) {
                return redirect()->route('login')
                    ->withErrors(['phone' => 'الجلسة منتهية']);
            }

            $lastSent = session('otp_sent_at');
            if ($lastSent && now()->diffInSeconds($lastSent) < 60) {
                $remaining = 60 - now()->diffInSeconds($lastSent);
                return back()->withErrors([
                    'otp' => "انتظر {$remaining} ثانية"
                ]);
            }

            if ($this->hasTooManyOtpRequests($phone)) {
                return back()->withErrors([
                    'otp' => "تجاوزت الحد المسموح"
                ]);
            }

            $code = rand(100000, 999999);
            Log::info('Login OTP resent', ['phone' => $phone, 'code' => $code]);
            Otp::updateOrCreate(
                ['phone' => $phone],
                [
                    'code' => $code,
                    'expires_at' => Carbon::now()->addMinutes(5),
                    'attempts' => 0
                ]
            );

            $whatsappSent = false;
            try {
                $response = app(WhatsAppService::class)
                    ->sendUsingAccessToken(
                        '2M94ka9d8Rjo9CFYUAIyi7lI0ank1pzG3tmsEGqp7tfymmr5beNMQr5WpN39',
                        $phone,
                        "كود التحقق الجديد: {$code}"
                    );
                $whatsappSent = $response['success'] ?? false;
            } catch (Exception $e) {
                Log::error('Resend WhatsApp error', ['error' => $e->getMessage()]);
            }

            $this->incrementOtpRequestCount($phone);
            session(['otp_sent_at' => now(), 'debug_code' => $code]);

            Log::info('OTP resent', ['code' => $code]);

            if ($whatsappSent) {
                return back()->with('success', 'تم إرسال كود جديد');
            } else {
                return back()->with('warning', "الكود الجديد: {$code}");
            }

        } catch (Exception $e) {
            Log::error('Resend error', ['error' => $e->getMessage()]);
            return back()->withErrors(['otp' => 'حدث خطأ']);
        }
    }

    private function hasTooManyOtpRequests(string $phone): bool
    {
        $key = 'otp_requests:' . $phone;
        return cache()->get($key, 0) >= 3;
    }

    private function incrementOtpRequestCount(string $phone): void
    {
        $key = 'otp_requests:' . $phone;
        $attempts = cache()->get($key, 0);
        cache()->put($key, $attempts + 1, now()->addMinutes($this->otpLockoutMinutes));
    }

    private function clearOtpRequestCount(string $phone): void
    {
        cache()->forget('otp_requests:' . $phone);
    }
}

