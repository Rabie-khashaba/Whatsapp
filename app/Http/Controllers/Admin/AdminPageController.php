<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\EmailSetting;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use App\Models\Instance;
use App\Models\NotificationSetting;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionStatusService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    private const PAGE_VIEW_MAP = [
        'customers' => 'admins.admin-customers',
        'customer-details' => 'admins.admin-customer-details',
        'plans' => 'admins.admin-plans',
        'subscriptions' => 'admins.admin-subscriptions',
        'payments-queue' => 'admins.admin-payments-queue',
        'payments' => 'admins.admin-payments',
        'invoices' => 'admins.admin-invoices',
        'reports' => 'admins.admin-reports',
        'admins' => 'admins.admin-admins',
        'settings' => 'admins.admin-settings',
        'profile' => 'admins.admin-profile',
    ];

    public function dashboard(): View
    {
        $this->syncExpiredCustomerStatuses();

        return view('admins.admin-dashboard', $this->dashboardData());
    }

    public function customers(Request $request): View
    {
        $this->syncExpiredCustomerStatuses();

        $query = Customer::query()->with('user');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->string('plan'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $pendingCustomers = Customer::where('status', 'pending')->count();
        $expiredCustomers = Customer::where('status', 'expired')->count();
        $cancelledCustomers = Customer::where('status', 'cancelled')->count();

        return view('admins.admin-customers', array_merge(
            $this->dashboardData(),
            compact(
                'customers',
                'totalCustomers',
                'activeCustomers',
                'pendingCustomers',
                'expiredCustomers',
                'cancelledCustomers'
            )
        ));
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'plan' => ['required', 'string', 'max:50'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'max_instances' => ['required', 'integer', 'min:1', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $existingCustomer = Customer::where('phone', $validated['phone'])->first();
        if ($existingCustomer) {
            return redirect()
                ->route('admin.customers')
                ->withErrors(['phone' => 'Customer with this phone already exists.'])
                ->withInput();
        }

        $plainPassword = Str::random(10);
        $createdUser = false;

        $user = User::where('phone', $validated['phone'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'country_code' => $validated['country_code'] ?? null,
                'type' => 'user',
                'password' => Hash::make($plainPassword),
            ]);
            $createdUser = true;
        } elseif ($user->type !== 'admin') {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $user->email,
                'country_code' => $validated['country_code'] ?? $user->country_code,
            ]);
        }

        if ($user->type === 'admin') {
            return redirect()
                ->route('admin.customers')
                ->withErrors(['phone' => 'This phone belongs to an admin account and cannot be used as customer.'])
                ->withInput();
        }

        Customer::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'country_code' => $validated['country_code'] ?? null,
            'plan' => $validated['plan'],
            'status' => 'active',
            'expiry_date' => $validated['expiry_date'] ?? null,
            'max_instances' => $validated['max_instances'],
            'billing_cycle' => $validated['billing_cycle'],
        ]);

        $successMessage = 'Customer added successfully.';
        $warningMessage = null;

        if ($createdUser) {
            try {
                $normalizedPhone = PhoneHelper::normalizeEgyptPhone($validated['phone']);
                $message = "Welcome {$validated['name']}\n"
                    . "Your account is ready.\n"
                    . "Phone: {$validated['phone']}\n"
                    . ($user->email ? "Email: {$user->email}\n" : '')
                    . "Password: {$plainPassword}\n"
                    . "Login: " . url('/login') . "\n"
                    . "After login, open dashboard and complete WhatsApp scan.";

                $response = app(WhatsAppService::class)->sendUsingAccessToken(
                    config('services.whatsapp.access_token'),
                    $normalizedPhone,
                    $message
                );

                if (!($response['success'] ?? false)) {
                    $warningMessage = 'Customer created, but WhatsApp message was not sent.';
                    Log::warning('Failed to send customer credentials via WhatsApp', [
                        'phone' => $validated['phone'],
                        'response' => $response,
                    ]);
                } else {
                    $successMessage .= ' Onboarding message sent on WhatsApp.';
                }
            } catch (\Throwable $e) {
                $warningMessage = 'Customer created, but WhatsApp send failed.';
                Log::error('WhatsApp credentials send exception', [
                    'phone' => $validated['phone'],
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $successMessage .= ' Existing user account linked.';
        }

        $redirect = redirect()
            ->route('admin.customers')
            ->with('success', $successMessage);

        if ($warningMessage) {
            $redirect->with('warning', $warningMessage);
        }

        return $redirect;
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,expired,cancelled'],
            'max_instances' => ['required', 'integer', 'min:1', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $originalStatus = (string) $customer->status;
        $originalMaxInstances = (int) $customer->max_instances;
        $originalExpiryDate = optional($customer->expiry_date)?->format('Y-m-d');
        $newExpiryDate = $validated['expiry_date'] ?? null;
        $passwordChanged = !empty($validated['password']);
        $newStatus = $validated['status'];

        if ($newExpiryDate && $newExpiryDate < now()->toDateString()) {
            $newStatus = 'expired';
        }

        $customer->update([
            'status' => $newStatus,
            'max_instances' => $validated['max_instances'],
            'expiry_date' => $newExpiryDate,
        ]);

        if ($passwordChanged && $customer->user) {
            $customer->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $changes = [];

        if ($originalStatus !== $newStatus) {
            $changes[] = "Status: " . ucfirst($originalStatus) . " -> " . ucfirst($newStatus);
        }

        if ($originalMaxInstances !== (int) $validated['max_instances']) {
            $changes[] = "Max instances: {$originalMaxInstances} -> {$validated['max_instances']}";
        }

        if ($originalExpiryDate !== $newExpiryDate) {
            $oldExpiryText = $originalExpiryDate ?: 'Not set';
            $newExpiryText = $newExpiryDate ?: 'Not set';
            $changes[] = "Expiry date: {$oldExpiryText} -> {$newExpiryText}";
        }

        if ($passwordChanged) {
            $changes[] = "New password: {$validated['password']}";
        }

        $successMessage = 'Customer updated successfully.';
        $warningMessage = null;

        if (!empty($changes)) {
            try {
                $normalizedPhone = PhoneHelper::normalizeEgyptPhone((string) $customer->phone);
                $message = "Hello {$customer->name}\n"
                    . "Your account settings were updated:\n- "
                    . implode("\n- ", $changes);

                $response = app(WhatsAppService::class)->sendUsingAccessToken(
                    config('services.whatsapp.access_token'),
                    $normalizedPhone,
                    $message
                );

                if ($response['success'] ?? false) {
                    $successMessage .= ' Update notification sent on WhatsApp.';
                } else {
                    $warningMessage = 'Customer updated, but WhatsApp notification was not sent.';
                    Log::warning('Failed to send customer update notification via WhatsApp', [
                        'customer_id' => $customer->id,
                        'phone' => $customer->phone,
                        'response' => $response,
                    ]);
                }
            } catch (\Throwable $e) {
                $warningMessage = 'Customer updated, but WhatsApp notification failed.';
                Log::error('Customer update WhatsApp notification exception', [
                    'customer_id' => $customer->id,
                    'phone' => $customer->phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $redirect = redirect()
            ->route('admin.customers')
            ->with('success', $successMessage);

        if ($warningMessage) {
            $redirect->with('warning', $warningMessage);
        }

        return $redirect;
    }

    public function plans(): View
    {
        $plans = Plan::query()
            ->latest()
            ->get()
            ->map(function (Plan $plan) {
                $subscribers = Customer::query()
                    ->whereRaw('LOWER(`plan`) = ?', [Str::lower($plan->name)])
                    ->count();

                $rawFeatures = $plan->features ?? [];
                $features = collect($rawFeatures)
                    ->filter(fn ($feature) => filled($feature))
                    ->values()
                    ->all();

                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description ?: 'Subscription plan',
                    'monthly_price' => (float) ($plan->monthly_price ?? 0),
                    'yearly_price' => (float) ($plan->yearly_price ?? 0),
                    'max_instances' => (int) ($plan->max_instances ?? 0),
                    'max_messages' => (int) ($plan->max_messages ?? 0),
                    'max_campaigns' => (int) ($plan->max_campaigns ?? 0),
                    'color' => $plan->color ?: 'secondary',
                    'features' => $features,
                    'features_text' => implode("\n", $features),
                    'subscribers' => $subscribers,
                    'active' => (bool) $plan->is_active,
                ];
            });

        return view('admins.admin-plans', array_merge($this->dashboardData(), [
            'plans' => $plans,
        ]));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:plans,name'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_instances' => ['required', 'integer', 'min:1'],
            'max_messages' => ['required', 'integer', 'min:0'],
            'max_campaigns' => ['required', 'integer', 'min:0'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['features'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(4)),
            'price' => $validated['monthly_price'],
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'monthly_price' => $validated['monthly_price'],
            'yearly_price' => $validated['yearly_price'],
            'max_instances' => $validated['max_instances'],
            'max_messages' => $validated['max_messages'],
            'max_campaigns' => $validated['max_campaigns'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.plans')->with('success', 'Plan created successfully.');
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('plans', 'name')->ignore($plan->id)],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_instances' => ['required', 'integer', 'min:1'],
            'max_messages' => ['required', 'integer', 'min:0'],
            'max_campaigns' => ['required', 'integer', 'min:0'],
            'color' => ['required', Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['features'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $plan->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['monthly_price'],
            'billing_cycle' => 'monthly',
            'monthly_price' => $validated['monthly_price'],
            'yearly_price' => $validated['yearly_price'],
            'max_instances' => $validated['max_instances'],
            'max_messages' => $validated['max_messages'],
            'max_campaigns' => $validated['max_campaigns'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.plans')->with('success', 'Plan updated successfully.');
    }

    public function togglePlanStatus(Plan $plan): RedirectResponse
    {
        $plan->update([
            'is_active' => !$plan->is_active,
        ]);

        return redirect()->route('admin.plans')->with('success', 'Plan status updated successfully.');
    }

    public function showBySlug(string $page): View
    {
        if ($page === 'customer-details') {
            return $this->customerDetails(request());
        }
        if ($page === 'admins') {
            return $this->admins();
        }
        if ($page === 'profile') {
            return $this->profile();
        }
        if ($page === 'reports') {
            return $this->reports(request());
        }
        if ($page === 'subscriptions') {
            return $this->subscriptions(request());
        }
        if ($page === 'payments-queue') {
            return $this->paymentsQueue(request());
        }
        if ($page === 'payments') {
            return $this->payments(request());
        }
        if ($page === 'invoices') {
            return $this->invoices(request());
        }
        if ($page === 'settings') {
            return $this->settings();
        }

        $view = self::PAGE_VIEW_MAP[$page] ?? null;
        abort_unless($view, 404);

        return view($view, $this->dashboardData());
    }

    public function customerDetails(Request $request): View
    {
        $this->syncExpiredCustomerStatuses();

        $customerId = (int) $request->integer('id');
        abort_if($customerId <= 0, 404);

        $customer = Customer::query()
            ->with('user')
            ->findOrFail($customerId);

        $instances = Instance::query()
            ->where('user_id', $customer->user_id)
            ->latest()
            ->get();

        $subscriptions = Subscription::query()
            ->with('plan')
            ->where('customer_id', $customer->id)
            ->latest('end_date')
            ->get();

        $latestSubscription = $subscriptions->first();

        $payments = Payment::query()
            ->where('customer_id', $customer->id)
            ->latest('paid_at')
            ->get();

        $invoices = Invoice::query()
            ->where('customer_id', $customer->id)
            ->latest('issued_at')
            ->get();

        $customerStats = [
            'active_instances' => $instances->where('status', 'connected')->count(),
            'total_instances' => $instances->count(),
            'total_payments' => $payments->count(),
            'total_invoices' => $invoices->count(),
            'total_paid' => (float) $payments->where('status', 'approved')->sum('amount'),
        ];

        return view('admins.admin-customer-details', array_merge(
            $this->dashboardData(),
            compact('customer', 'instances', 'subscriptions', 'latestSubscription', 'payments', 'invoices', 'customerStats')
        ));
    }

    public function profile(): View
    {
        /** @var User $admin */
        $admin = auth()->user();

        $profileStats = [
            'customers_managed' => Customer::count(),
            'payments_approved' => Payment::where('status', 'approved')->count(),
            'reports_generated' => Invoice::count(),
        ];
        $profileStats['total_actions'] = array_sum($profileStats);

        $admin->setAttribute('display_role', $this->adminDisplayRole($admin));
        $admin->setAttribute('initials', $this->adminInitials($admin->name));

        return view('admins.admin-profile', array_merge(
            $this->dashboardData(),
            compact('admin', 'profileStats')
        ));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($admin->id)],
            'country_code' => ['nullable', 'string', 'max:5'],
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => $validated['country_code'] ?? null,
        ]);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    public function updateProfilePassword(Request $request): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Password changed successfully.');
    }

    public function reports(Request $request): View
    {
        [$dateFrom, $dateTo, $period] = $this->resolveReportPeriod($request);
        $previousFrom = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo) + 1);
        $previousTo = $dateFrom->copy()->subDay();

        $customersInPeriod = Customer::query()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

        $totalCustomers = (clone $customersInPeriod)->count();
        $activeCustomers = Customer::query()->where('status', 'active')->count();
        $expiredCustomers = Customer::query()->where('status', 'expired')->count();
        $totalRevenue = (float) Payment::query()
            ->where('status', 'approved')
            ->whereBetween('paid_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->sum('amount');

        $previousCustomers = Customer::query()
            ->whereBetween('created_at', [$previousFrom->copy()->startOfDay(), $previousTo->copy()->endOfDay()])
            ->count();
        $previousRevenue = (float) Payment::query()
            ->where('status', 'approved')
            ->whereBetween('paid_at', [$previousFrom->copy()->startOfDay(), $previousTo->copy()->endOfDay()])
            ->sum('amount');

        $customerGrowth = $this->percentageChange($totalCustomers, $previousCustomers);
        $revenueGrowth = $this->percentageChange($totalRevenue, $previousRevenue);

        $monthlyRevenue = Payment::query()
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month_key, SUM(amount) as total")
            ->where('status', 'approved')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->map(function ($row) {
                return [
                    'label' => Carbon::createFromFormat('Y-m', $row->month_key)->format('M Y'),
                    'total' => (float) $row->total,
                ];
            });

        $maxRevenuePoint = max(1, (float) $monthlyRevenue->max('total'));

        $planDistribution = Customer::query()
            ->selectRaw('plan, COUNT(*) as total')
            ->whereNotNull('plan')
            ->where('plan', '!=', '')
            ->groupBy('plan')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'plan' => $row->plan ?: 'Unknown',
                    'total' => (int) $row->total,
                ];
            });

        $planTotal = max(1, $planDistribution->sum('total'));
        $planColors = ['secondary', 'primary', 'warning', 'success', 'danger', 'info'];
        $planDistribution = $planDistribution->values()->map(function (array $item, int $index) use ($planTotal, $planColors) {
            $item['percentage'] = round(($item['total'] / $planTotal) * 100, 1);
            $item['color'] = $planColors[$index % count($planColors)];
            return $item;
        });

        $topCustomers = Payment::query()
            ->with('customer')
            ->selectRaw('customer_id, SUM(amount) as total_paid, MAX(COALESCE(paid_at, created_at)) as last_payment_at')
            ->where('status', 'approved')
            ->groupBy('customer_id')
            ->orderByDesc('total_paid')
            ->take(5)
            ->get();

        return view('admins.admin-reports', array_merge(
            $this->dashboardData(),
            compact(
                'period',
                'dateFrom',
                'dateTo',
                'totalCustomers',
                'activeCustomers',
                'expiredCustomers',
                'totalRevenue',
                'customerGrowth',
                'revenueGrowth',
                'monthlyRevenue',
                'maxRevenuePoint',
                'planDistribution',
                'topCustomers'
            )
        ));
    }

    public function exportReports(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        [$dateFrom, $dateTo] = $this->resolveReportPeriod($request);

        $rows = Payment::query()
            ->with('customer')
            ->where('status', 'approved')
            ->whereBetween('paid_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->orderByDesc('paid_at')
            ->get()
            ->map(function (Payment $payment) {
                return [
                    'customer' => $payment->customer?->name ?? '-',
                    'email' => $payment->customer?->email ?? '-',
                    'amount' => number_format((float) $payment->amount, 2, '.', ''),
                    'method' => $payment->method,
                    'paid_at' => optional($payment->paid_at)->format('Y-m-d H:i:s') ?? '-',
                ];
            });

        $filename = 'report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Customer', 'Email', 'Amount', 'Method', 'Paid At']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function admins(): View
    {
        $admins = User::query()
            ->where('type', 'admin')
            ->latest()
            ->get()
            ->map(function (User $admin, int $index) {
                $initials = collect(explode(' ', trim($admin->name)))
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                    ->implode('');

                $admin->setAttribute('display_role', $index === 0 ? 'super admin' : 'admin');
                $admin->setAttribute('initials', $initials ?: 'A');

                return $admin;
            });

        $totalAdmins = $admins->count();
        $activeAdmins = $totalAdmins;
        $latestAdmin = $admins->first();

        return view('admins.admin-admins', array_merge(
            $this->dashboardData(),
            compact('admins', 'totalAdmins', 'activeAdmins', 'latestAdmin')
        ));
    }

    public function settings(): View
    {
        $generalSettings = $this->generalSettings();
        $paymentSettings = $this->paymentSettings();
        $emailSettings = $this->emailSettings();
        $notificationSettings = $this->notificationSettings();

        return view('admins.admin-settings', array_merge(
            $this->dashboardData(),
            compact('generalSettings', 'paymentSettings', 'emailSettings', 'notificationSettings')
        ));
    }

    public function updateSettings(Request $request, string $section): RedirectResponse
    {
        $section = strtolower($section);
        $redirect = redirect()
            ->route('admin.settings', ['tab' => $section])
            ->with('success', ucfirst(str_replace('-', ' ', $section)) . ' settings saved successfully.');

        if ($section === 'general') {
            $settings = $this->generalSettings();
            $validated = $request->validate([
                'site_name' => ['required', 'string', 'max:255'],
                'site_url' => ['nullable', 'url', 'max:255'],
                'support_email' => ['nullable', 'email', 'max:255'],
                'support_phone' => ['nullable', 'string', 'max:50'],
                'site_description' => ['nullable', 'string', 'max:2000'],
            ]);

            $settings->update($validated);

            return $redirect;
        }

        if ($section === 'payment') {
            $settings = $this->paymentSettings();
            $validated = $request->validate([
                'vodafone_cash_number' => ['nullable', 'string', 'max:50'],
                'bank_name' => ['nullable', 'string', 'max:255'],
                'bank_account_number' => ['nullable', 'string', 'max:255'],
                'bank_iban' => ['nullable', 'string', 'max:255'],
            ]);

            $settings->update(array_merge($validated, [
                'vodafone_cash_enabled' => $request->boolean('vodafone_cash_enabled'),
                'bank_transfer_enabled' => $request->boolean('bank_transfer_enabled'),
                'credit_card_enabled' => $request->boolean('credit_card_enabled'),
            ]));

            return $redirect;
        }

        if ($section === 'email') {
            $settings = $this->emailSettings();
            $validated = $request->validate([
                'smtp_host' => ['nullable', 'string', 'max:255'],
                'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
                'smtp_username' => ['nullable', 'string', 'max:255'],
                'smtp_password' => ['nullable', 'string', 'max:255'],
                'from_email' => ['nullable', 'email', 'max:255'],
                'from_name' => ['nullable', 'string', 'max:255'],
            ]);

            $settings->update($validated);

            return $redirect;
        }

        if ($section === 'notifications') {
            $settings = $this->notificationSettings();
            $settings->update([
                'notify_new_customer' => $request->boolean('notify_new_customer'),
                'notify_new_payment' => $request->boolean('notify_new_payment'),
                'notify_expiring' => $request->boolean('notify_expiring'),
                'notify_expired' => $request->boolean('notify_expired'),
            ]);

            return $redirect;
        }

        return redirect()->route('admin.settings')->withErrors([
            'error' => 'Invalid settings section.',
        ]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => $validated['country_code'] ?? null,
            'password' => Hash::make($validated['password']),
            'type' => 'admin',
        ]);

        return redirect()->route('admin.admins')->with('success', 'Admin user created successfully.');
    }

    public function updateAdmin(Request $request, User $admin): RedirectResponse
    {
        if ($admin->type !== 'admin') {
            return redirect()->route('admin.admins')->withErrors([
                'error' => 'Selected user is not an admin.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($admin->id)],
            'country_code' => ['nullable', 'string', 'max:5'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => $validated['country_code'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $admin->update($payload);

        return redirect()->route('admin.admins')->with('success', 'Admin user updated successfully.');
    }

    public function deleteAdmin(User $admin): RedirectResponse
    {
        if ($admin->type !== 'admin') {
            return redirect()->route('admin.admins')->withErrors([
                'error' => 'Selected user is not an admin.',
            ]);
        }

        if ((int) $admin->id === (int) auth()->id()) {
            return redirect()->route('admin.admins')->withErrors([
                'error' => 'You cannot delete the currently logged-in admin.',
            ]);
        }

        $adminsCount = User::where('type', 'admin')->count();
        if ($adminsCount <= 1) {
            return redirect()->route('admin.admins')->withErrors([
                'error' => 'At least one admin account must remain in the system.',
            ]);
        }

        $admin->delete();

        return redirect()->route('admin.admins')->with('success', 'Admin user deleted successfully.');
    }

    public function subscriptions(Request $request): View
    {
        $today = Carbon::today();

        $query = Subscription::query()
            ->with(['customer', 'plan'])
            ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
            ->select('subscriptions.*', 'customers.billing_cycle as customer_billing_cycle');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('customers.name', 'like', "%{$q}%")
                    ->orWhere('customers.email', 'like', "%{$q}%")
                    ->orWhere('customers.phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('plan')) {
            $planFilter = Str::lower((string) $request->string('plan'));
            $query->where(function ($sub) use ($planFilter) {
                $sub->whereHas('plan', function ($planQuery) use ($planFilter) {
                    $planQuery->whereRaw('LOWER(name) = ?', [$planFilter]);
                })->orWhereRaw('LOWER(customers.plan) = ?', [$planFilter]);
            });
        }

        if ($request->filled('billing_cycle')) {
            $query->where('customers.billing_cycle', $request->string('billing_cycle'));
        }

        $statusFilter = (string) $request->string('status');
        if ($statusFilter !== '') {
            if ($statusFilter === 'expiring') {
                $query->where('subscriptions.status', 'active')
                    ->whereBetween('subscriptions.end_date', [$today->copy(), $today->copy()->addDays(7)]);
            } elseif ($statusFilter === 'active') {
                $query->where('subscriptions.status', 'active')
                    ->whereDate('subscriptions.end_date', '>=', $today);
            } elseif ($statusFilter === 'expired') {
                $query->where(function ($sub) use ($today) {
                    $sub->where('subscriptions.status', 'expired')
                        ->orWhereDate('subscriptions.end_date', '<', $today);
                });
            } else {
                $query->where('subscriptions.status', $statusFilter);
            }
        }

        $subscriptions = $query
            ->orderBy('subscriptions.end_date')
            ->paginate(10)
            ->withQueryString();

        $activeSubscriptionsCount = Subscription::query()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->count();

        $expiringSoonCount = Subscription::query()
            ->where('status', 'active')
            ->whereBetween('end_date', [$today->copy(), $today->copy()->addDays(7)])
            ->count();

        $monthlyRenewalsCount = Subscription::query()
            ->where('status', 'active')
            ->whereMonth('end_date', Carbon::now()->month)
            ->whereYear('end_date', Carbon::now()->year)
            ->count();

        $mrr = Subscription::query()
            ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
            ->where('subscriptions.status', 'active')
            ->whereDate('subscriptions.end_date', '>=', $today)
            ->selectRaw(
                "SUM(CASE WHEN customers.billing_cycle = 'yearly' THEN subscriptions.price / 12 ELSE subscriptions.price END) as mrr_total"
            )
            ->value('mrr_total') ?? 0;

        $planNames = Plan::query()->orderBy('name')->pluck('name');

        return view('admins.admin-subscriptions', array_merge(
            $this->dashboardData(),
            [
                'subscriptions' => $subscriptions,
                'activeSubscriptionsCount' => $activeSubscriptionsCount,
                'expiringSoonCount' => $expiringSoonCount,
                'monthlyRenewalsCount' => $monthlyRenewalsCount,
                'mrr' => $mrr,
                'planNames' => $planNames,
                'statusFilter' => $statusFilter,
            ]
        ));
    }

    public function renewSubscription(Subscription $subscription): RedirectResponse
    {
        $customer = $subscription->customer;
        $billingCycle = $customer?->billing_cycle ?? 'monthly';
        $startDate = Carbon::parse($subscription->end_date)->isPast()
            ? Carbon::today()
            : Carbon::parse($subscription->end_date)->addDay();

        $newEndDate = $billingCycle === 'yearly'
            ? $startDate->copy()->addYear()->subDay()
            : $startDate->copy()->addMonth()->subDay();

        $subscription->update([
            'status' => 'active',
            'start_date' => $startDate->toDateString(),
            'end_date' => $newEndDate->toDateString(),
            'expiring_notified_at' => null,
            'expired_notified_at' => null,
        ]);

        app(SubscriptionStatusService::class)->syncSubscription($subscription);

        return redirect()->back()->with('success', 'Subscription renewed successfully.');
    }

    public function cancelSubscription(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status' => 'cancelled',
        ]);

        app(SubscriptionStatusService::class)->syncSubscription($subscription);

        return redirect()->back()->with('success', 'Subscription cancelled successfully.');
    }

    public function paymentsQueue(Request $request): View
    {
        $query = Payment::query()
            ->with(['customer', 'subscription.plan', 'invoice'])
            ->where('status', 'pending');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->whereHas('customer', function ($customerQuery) use ($q) {
                $customerQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('method')) {
            $query->where('method', $request->string('method'));
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        $pendingCount = Payment::where('status', 'pending')->count();
        $pendingAmount = Payment::where('status', 'pending')->sum('amount');
        $vodafoneCount = Payment::where('status', 'pending')->where('method', 'vodafone_cash')->count();
        $bankCount = Payment::where('status', 'pending')->where('method', 'bank_transfer')->count();

        return view('admins.admin-payments-queue', array_merge(
            $this->dashboardData(),
            compact('payments', 'pendingCount', 'pendingAmount', 'vodafoneCount', 'bankCount')
        ));
    }

    public function payments(Request $request): View
    {
        $query = Payment::query()->with(['customer', 'subscription.plan', 'invoice']);

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->whereHas('customer', function ($customerQuery) use ($q) {
                $customerQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->string('method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        $totalCompleted = Payment::where('status', 'approved')->count();
        $totalRevenue = Payment::where('status', 'approved')->sum('amount');
        $monthlyRevenue = Payment::where('status', 'approved')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');
        $totalRejected = Payment::where('status', 'rejected')->count();

        return view('admins.admin-payments', array_merge(
            $this->dashboardData(),
            compact('payments', 'totalCompleted', 'totalRevenue', 'monthlyRevenue', 'totalRejected')
        ));
    }

    public function invoices(Request $request): View
    {
        $query = Invoice::query()->with(['customer', 'subscription.plan', 'payment']);

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($invoiceQuery) use ($q) {
                $invoiceQuery->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($q) {
                        $customerQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $invoices = $query->latest('issued_at')->paginate(10)->withQueryString();

        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $totalAmount = Invoice::where('status', 'paid')->sum('amount');

        return view('admins.admin-invoices', array_merge(
            $this->dashboardData(),
            compact('invoices', 'totalInvoices', 'paidInvoices', 'totalAmount')
        ));
    }

    public function showInvoice(Invoice $invoice): View
    {
        $invoice->load(['customer', 'subscription.plan', 'payment']);

        return view('admins.invoice-show', array_merge(
            $this->dashboardData(),
            [
                'invoice' => $invoice,
                'isPrintMode' => request()->boolean('print'),
            ]
        ));
    }

    public function approvePayment(Payment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'Only pending payments can be approved.']);
        }

        $customer = $payment->customer;
        if (!$customer) {
            return redirect()->back()->withErrors(['error' => 'Customer not found for this payment.']);
        }

        $subscription = $payment->subscription;
        if (!$subscription) {
            $subscription = Subscription::where('customer_id', $customer->id)
                ->latest('end_date')
                ->first();
        }

        if (!$subscription) {
            $plan = Plan::whereRaw('LOWER(name) = ?', [Str::lower((string) $customer->plan)])->first();
            $startDate = Carbon::today();
            $billingCycle = $customer->billing_cycle === 'yearly' ? 'yearly' : 'monthly';
            $endDate = ($billingCycle === 'yearly')
                ? $startDate->copy()->addYear()->subDay()
                : $startDate->copy()->addMonth()->subDay();

            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan?->id,
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => $payment->amount,
                'billing_cycle' => $billingCycle,
            ]);
        } else {
            $billingCycle = $subscription->billing_cycle ?? $customer->billing_cycle ?? 'monthly';
            $startDate = Carbon::parse($subscription->end_date)->isPast()
                ? Carbon::today()
                : Carbon::parse($subscription->end_date)->addDay();

            $endDate = ($billingCycle === 'yearly')
                ? $startDate->copy()->addYear()->subDay()
                : $startDate->copy()->addMonth()->subDay();

            $subscription->update([
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => $payment->amount,
                'billing_cycle' => $billingCycle,
                'expiring_notified_at' => null,
                'expired_notified_at' => null,
            ]);
        }

        if ($subscription->plan) {
            $customer->update([
                'plan' => $subscription->plan->name,
                'billing_cycle' => $subscription->billing_cycle ?? 'monthly',
                'max_instances' => $subscription->plan->max_instances ?? $customer->max_instances,
            ]);
        }

        app(SubscriptionStatusService::class)->syncSubscription($subscription);

        $payment->update([
            'subscription_id' => $subscription->id,
            'status' => 'approved',
            'paid_at' => Carbon::now(),
        ]);

        $nextInvoiceId = (int) (Invoice::max('id') ?? 0) + 1;
        $invoiceNumber = 'INV-' . Carbon::now()->format('Y') . '-' . str_pad((string) $nextInvoiceId, 4, '0', STR_PAD_LEFT);

        Invoice::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => 'paid',
            'issued_at' => Carbon::today()->toDateString(),
            'due_at' => Carbon::today()->toDateString(),
            'paid_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Payment approved successfully.');
    }

    public function rejectPayment(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'Only pending payments can be rejected.']);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'notes' => trim(($payment->notes ? $payment->notes . "\n" : '') . ($validated['reason'] ?? 'Rejected by admin')),
        ]);

        return redirect()->back()->with('success', 'Payment rejected successfully.');
    }

    private function dashboardData(): array
    {
        $this->syncExpiredCustomerStatuses();

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $expiredCustomers = Customer::where('status', 'expired')->count();
        $cancelledCustomers = Customer::where('status', 'cancelled')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->count();

        $monthlyRevenue = Payment::where('status', 'approved')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');

        $totalRevenue = Payment::where('status', 'approved')->sum('amount');

        $recentCustomers = Customer::query()
            ->latest()
            ->take(8)
            ->get(['id', 'name', 'phone', 'plan', 'status', 'created_at']);

        return compact(
            'totalCustomers',
            'activeCustomers',
            'expiredCustomers',
            'cancelledCustomers',
            'pendingPayments',
            'expiringSoon',
            'monthlyRevenue',
            'totalRevenue',
            'recentCustomers'
        );
    }

    private function generalSettings(): GeneralSetting
    {
        return GeneralSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'WhatsApp Campaign Platform',
                'site_url' => config('app.url'),
                'support_email' => 'support@example.com',
                'support_phone' => '+20 100 000 0000',
                'site_description' => 'WhatsApp marketing automation platform',
            ]
        );
    }

    private function paymentSettings(): PaymentSetting
    {
        return PaymentSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'vodafone_cash_enabled' => true,
                'bank_transfer_enabled' => true,
                'credit_card_enabled' => true,
            ]
        );
    }

    private function emailSettings(): EmailSetting
    {
        return EmailSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => 'noreply@example.com',
                'smtp_password' => '',
                'from_email' => 'noreply@example.com',
                'from_name' => 'WhatsApp Platform',
            ]
        );
    }

    private function notificationSettings(): NotificationSetting
    {
        return NotificationSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'notify_new_customer' => true,
                'notify_new_payment' => true,
                'notify_expiring' => true,
                'notify_expired' => true,
            ]
        );
    }

    private function adminDisplayRole(User $admin): string
    {
        $firstAdminId = User::query()
            ->where('type', 'admin')
            ->orderBy('id')
            ->value('id');

        return (int) $admin->id === (int) $firstAdminId ? 'super admin' : 'admin';
    }

    private function adminInitials(string $name): string
    {
        $initials = collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials ?: 'A';
    }

    private function resolveReportPeriod(Request $request): array
    {
        $period = (string) $request->string('period', 'this_month');
        $today = Carbon::today();

        return match ($period) {
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth(), $period],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), $period],
            'last_year' => [$today->copy()->subYear()->startOfYear(), $today->copy()->subYear()->endOfYear(), $period],
            'custom' => [
                Carbon::parse($request->string('date_from', $today->copy()->startOfMonth()->toDateString())),
                Carbon::parse($request->string('date_to', $today->toDateString())),
                $period,
            ],
            default => [$today->copy()->startOfMonth(), $today->copy(), 'this_month'],
        };
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function syncExpiredCustomerStatuses(): void
    {
        app(SubscriptionStatusService::class)->syncAll();
    }
}
