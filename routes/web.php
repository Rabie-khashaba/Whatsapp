<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Auth\OtpLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OTP Login Routes
|--------------------------------------------------------------------------
*/
// ---------- OTP Login ----------
Route::get('/login', [OtpLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [OtpLoginController::class, 'login']);
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

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::get('/login.html', fn () => redirect()->route('admin.login'));
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminPageController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard.html', fn () => redirect()->route('admin.dashboard'));
        Route::get('/customers', [AdminPageController::class, 'customers'])->name('customers');
        Route::post('/customers', [AdminPageController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/customers/{customer}', [AdminPageController::class, 'updateCustomer'])->name('customers.update');
        Route::get('/customers.html', fn () => redirect()->route('admin.customers'));
        Route::get('/plans', [AdminPageController::class, 'plans'])->name('plans');
        Route::post('/plans', [AdminPageController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [AdminPageController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{plan}/toggle-status', [AdminPageController::class, 'togglePlanStatus'])->name('plans.toggle');
        Route::get('/plans.html', fn () => redirect()->route('admin.plans'));
        Route::get('/admins', [AdminPageController::class, 'admins'])->name('admins');
        Route::post('/admins', [AdminPageController::class, 'storeAdmin'])->name('admins.store');
        Route::put('/admins/{admin}', [AdminPageController::class, 'updateAdmin'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminPageController::class, 'deleteAdmin'])->name('admins.delete');
        Route::get('/admins.html', fn () => redirect()->route('admin.admins'));
        Route::get('/settings', [AdminPageController::class, 'settings'])->name('settings');
        Route::post('/settings/{section}', [AdminPageController::class, 'updateSettings'])->name('settings.update');
        Route::get('/settings.html', fn () => redirect()->route('admin.settings'));
        Route::get('/reports', [AdminPageController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [AdminPageController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports.html', fn () => redirect()->route('admin.reports'));
        Route::get('/subscriptions', [AdminPageController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions/{subscription}/renew', [AdminPageController::class, 'renewSubscription'])->name('subscriptions.renew');
        Route::post('/subscriptions/{subscription}/cancel', [AdminPageController::class, 'cancelSubscription'])->name('subscriptions.cancel');
        Route::get('/subscriptions.html', fn () => redirect()->route('admin.subscriptions'));
        Route::get('/payments-queue', [AdminPageController::class, 'paymentsQueue'])->name('payments.queue');
        Route::post('/payments/{payment}/approve', [AdminPageController::class, 'approvePayment'])->name('payments.approve');
        Route::post('/payments/{payment}/reject', [AdminPageController::class, 'rejectPayment'])->name('payments.reject');
        Route::get('/payments', [AdminPageController::class, 'payments'])->name('payments');
        Route::get('/invoices', [AdminPageController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}', [AdminPageController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/profile', [AdminPageController::class, 'profile'])->name('profile');
        Route::put('/profile', [AdminPageController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [AdminPageController::class, 'updateProfilePassword'])->name('profile.password');
        Route::get('/payments-queue.html', fn () => redirect()->route('admin.payments.queue'));
        Route::get('/payments.html', fn () => redirect()->route('admin.payments'));
        Route::get('/invoices.html', fn () => redirect()->route('admin.invoices'));
        Route::get('/{page}', [AdminPageController::class, 'showBySlug'])
            ->where('page', 'customers|customer-details|plans|subscriptions|payments-queue|payments|invoices|reports|admins|settings|profile')
            ->name('page');
        Route::get('/{page}.html', fn (string $page) => redirect()->route('admin.page', ['page' => $page]))
            ->where('page', 'customers|customer-details|plans|subscriptions|payments-queue|payments|invoices|reports|admins|settings|profile');
        Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout.get');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/admin-login.html', fn () => redirect()->route('admin.login'));
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin-dashboard.html', fn () => redirect()->route('admin.dashboard'));
    Route::get('/admin-{page}.html', fn (string $page) => redirect()->route('admin.page', ['page' => $page]))
        ->where('page', 'customers|customer-details|plans|subscriptions|payments-queue|payments|invoices|reports|admins|settings|profile');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [InstanceController::class, 'index'])->name('dashboard');

    // Instances Management
    Route::post('/instance', [InstanceController::class, 'store'])->middleware('subscription.active')->name('instance.store');
    Route::get('/instance/{id}', [InstanceController::class, 'show'])->name('instance.show');
    Route::get('/instance/{id}/check', [InstanceController::class, 'checkStatus'])->name('instance.check');
    Route::post('/instance/{id}/send', [InstanceController::class, 'sendMessage'])->middleware('subscription.active')->name('instance.send');
    Route::get('/instance/{id}/logout', [InstanceController::class, 'logout'])->name('instance.logout');
    Route::delete('/instance/{id}', [InstanceController::class, 'destroy'])->name('instance.destroy');
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions.html', fn () => redirect()->route('subscriptions.index'));
    Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
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
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings.edit');
    Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
