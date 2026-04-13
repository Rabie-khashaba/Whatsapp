<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\OtpLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InstanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OTP Login Routes
|--------------------------------------------------------------------------
*/
// ---------- OTP Login ----------
Route::get('/login', [OtpLoginController::class, 'showLoginForm'])->name('login');
Route::prefix('login')->group(function () {
    Route::post('/send-otp', [OtpLoginController::class, 'sendOtp'])->name('send.otp');
    Route::get('/verify-otp', [OtpLoginController::class, 'showOtpForm'])->name('verify.otp.login');
    Route::post('/verify-otp', [OtpLoginController::class, 'verifyOtp'])->name('verify.otp.post');
    Route::get('/resend-otp', [OtpLoginController::class, 'resendOtp'])->name('resend.otp');
});

// ---------- Register & OTP ----------
Route::prefix('register')->group(function () {
    Route::get('/', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/verify-otp', [RegisteredUserController::class, 'showOtpForm'])->name('verify.otp.form');
    Route::post('/verify-otp', [RegisteredUserController::class, 'verifyOtp'])->name('verify.otp.register');
    Route::get('/resend-otp', [RegisteredUserController::class, 'resendOtp'])->name('resend.otp.register');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [InstanceController::class, 'index'])->name('dashboard');

    // Instances Management
    Route::post('/instance', [InstanceController::class, 'store'])->name('instance.store');
    Route::get('/instance/{id}', [InstanceController::class, 'show'])->name('instance.show');
    Route::get('/instance/{id}/check', [InstanceController::class, 'checkStatus'])->name('instance.check');
    Route::post('/instance/{id}/send', [InstanceController::class, 'sendMessage'])->name('instance.send');
    Route::get('/instance/{id}/logout', [InstanceController::class, 'logout'])->name('instance.logout');
    Route::delete('/instance/{id}', [InstanceController::class, 'destroy'])->name('instance.destroy');
});


Route::get('/send-test', function () {
    $instance = App\Models\Instance::find(20);

    if (!$instance) {
        return 'No instance found';
    }

    if ($instance->status !== 'connected') {
        return 'Instance not connected. Scan QR first at: /instance/' . $instance->id;
    }

    $response = Http::post(
        config('services.baileys.url') . "/api/instance/{$instance->green_instance_id}/send",
        [
            'phone' => '201008781912',
            'message' => 'Test message from Baileys!'
        ]
    );

    return response()->json([
        'success' => $response->successful(),
        'status' => $response->status(),
        'data' => $response->json()
    ]);
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
