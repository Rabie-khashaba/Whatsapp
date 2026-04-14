<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PhoneHelper;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * عرض صفحة التسجيل
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * بدء التسجيل → فقط تحقق المستخدم + إرسال OTP
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'max:5'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // التحقق إذا كان المستخدم موجود بالفعل
        $existingUser = User::where('phone', $request->phone)
            ->orWhere('email', $request->email)
            ->first();

        if ($existingUser) {
            return back()->withErrors([
                'phone' => 'هذا الرقم مسجل بالفعل. يمكنك تسجيل الدخول بدلاً من ذلك.'
            ])->withInput();
        }

        // Generate OTP فقط
        $code = rand(100000, 999999);
        Log::info('Registration OTP generated', ['phone' => $request->phone, 'code' => $code]);
        Otp::updateOrCreate(
            ['phone' => $request->phone],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0
            ]
        );

        $phone = PhoneHelper::normalizeEgyptPhone($request->phone);

        app(WhatsAppService::class)->sendUsingAccessToken(
            config('services.whatsapp.access_token'),
            $phone,
            "كود التحقق: {$code}\nصالح لمدة 5 دقائق"
        );

        // تخزين البيانات مؤقتًا في session لاستخدامها بعد التحقق من OTP
        session([
            'otp_phone' => $request->phone,
            'otp_name' => $request->name,
            'otp_email' => $request->email,
            'otp_country_code' => $request->country_code,
            'otp_password' => $request->password,
        ]);

        return redirect()->route('verify.otp.form');
    }

    /**
     * عرض صفحة إدخال OTP
     */
    public function showOtpForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('register')
                ->withErrors(['phone' => 'الجلسة منتهية. ابدأ التسجيل من جديد.']);
        }

        return view('auth.register-otp', [
            'phone' => session('otp_phone')
        ]);
    }

    /**
     * إعادة إرسال OTP للتسجيل
     */
    public function resendOtp()
    {
        $phone = session('otp_phone');
        if (!$phone) {
            return redirect()->route('register');
        }

        $code = rand(100000, 999999);
        Log::info('Registration OTP resent', ['phone' => $phone, 'code' => $code]);
        Otp::updateOrCreate(
            ['phone' => $phone],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0
            ]
        );

        $normalizedPhone = \App\Helpers\PhoneHelper::normalizeEgyptPhone($phone);
        app(\App\Services\WhatsAppService::class)->sendUsingAccessToken(
            config('services.whatsapp.access_token'),
            $normalizedPhone,
            "كود التحقق الجديد: {$code}\nصالح لمدة 5 دقائق"
        );

        return back()->with('success', 'تم إعادة إرسال الكود');
    }

    /**
     * التحقق من OTP وإنشاء المستخدم بعد التأكد
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $phone = session('otp_phone');

        $otp = Otp::where('phone', $phone)
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'كود التحقق غير صحيح أو منتهي الصلاحية']);
        }

        // إنشاء User و Customer بعد التحقق
        $user = User::create([
            'name' => session('otp_name'),
            'email' => session('otp_email'),
            'phone' => session('otp_phone'),
            'country_code' => session('otp_country_code'),
            'password' => Hash::make(session('otp_password')),
        ]);

        Customer::create([
            'user_id' => $user->id,
            'name' => session('otp_name'),
            'email' => session('otp_email'),
            'phone' => session('otp_phone'),
            'country_code' => session('otp_country_code'),
        ]);

        // تنظيف البيانات من session
        session()->forget(['otp_phone', 'otp_name', 'otp_email', 'otp_country_code', 'otp_password']);

        // تسجيل الدخول مباشرة
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'تم إنشاء الحساب بنجاح');
    }
}
