<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Log;

class InstanceController extends Controller
{
    private $baileysUrl;

    public function __construct()
    {
        $this->baileysUrl = config('services.baileys.url', 'http://localhost:3000');
    }

    /**
     * ✅ عرض Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $instances = Instance::where('user_id', $user->id)->latest()->get();

        // إحصائيات لوحة التحكم
        $instanceIds = $instances->pluck('id');
        $totalMessages = MessageLog::whereIn('instance_id', $instanceIds)->count();
        $totalCampaigns = 0;
        $instanceLimit = 20;
        $customer = null;
        if ($user->type === 'user') {
            $customer = $user->customer;
            if ($customer) {
                $customer->updateTrialStatusIfExpired();
            }
            $instanceLimit = (int) optional($customer)->max_instances;
            if ($instanceLimit < 0) {
                $instanceLimit = 0;
            }
        }
        $remainingInstances = max(0, $instanceLimit - $instances->count());

        return view('whatsapp.dashboard', compact('instances', 'totalMessages', 'totalCampaigns', 'remainingInstances', 'instanceLimit', 'customer'));
    }

    /**
     * ✅ إنشاء Instance جديد في Baileys
     */
    public function apiIntegration()
    {
        $user = Auth::user();

        return view('whatsapp.api-integration', [
            'instances' => Instance::where('user_id', $user->id)->latest()->get(),
            'apiBaseUrl' => rtrim((string) config('services.whatsapp_api.url', config('app.url')), '/') . '/api/v2',
        ]);
    }

    private function createBaileysInstance($instanceId)
    {
        try {
            Log::info('Creating Baileys instance', ['instanceId' => $instanceId]);

            $response = Http::timeout(30)->post("{$this->baileysUrl}/api/instance/create", [
                'instanceId' => $instanceId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Baileys instance created successfully', $data);
                return true;
            }

            Log::error('Baileys Instance Creation Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Baileys Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ حفظ Instance جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'phone_number' => 'nullable|regex:/^[0-9]{10,15}$/'
        ]);

        $user = Auth::user();
        if ($user->type === 'user') {
            $customer = $user->customer;
            if ($customer) {
                $customer->updateTrialStatusIfExpired();
            }

            // Check if customer subscription is blocked (but allow if trial is active)
            $blockedStatuses = ['cancelled', 'expired', 'pending'];
            if ($customer && in_array($customer->status, $blockedStatuses) && !$customer->hasActiveTrial()) {
                return back()->withErrors([
                    'error' => 'Your subscription is not active. Please renew your subscription to continue using this service.'
                ])->withInput();
            }

            $instanceLimit = (int) optional($customer)->max_instances;
            $currentInstancesCount = Instance::where('user_id', $user->id)->count();

            if ($instanceLimit <= 0) {
                return back()->withErrors([
                    'error' => 'Your subscription does not allow creating instances. Please contact support.'
                ])->withInput();
            }

            if ($currentInstancesCount >= $instanceLimit) {
                return back()->withErrors([
                    'error' => "You reached your instance limit ({$instanceLimit}). Upgrade your plan to add more."
                ])->withInput();
            }
        }

        // ✅ التحقق من وجود الرقم مسبقاً
        if ($request->phone_number) {
            $exists = Instance::where('phone_number', $request->phone_number)->exists();
            if ($exists) {
                return back()->withErrors([
                    'phone_number' => 'الرقم موجود بالفعل، لا يمكن إنشاء مثيل بهذا الرقم. أدخل رقماً آخر.'
                ])->withInput();
            }
        }

        Log::info('User attempting to create instance', [
            'user_id' => Auth::id(),
            'name' => $request->name,
            'phone_number' => $request->phone_number,
        ]);

        $instanceId = 'instance_' . Str::random(10);

        if (!$this->createBaileysInstance($instanceId)) {
            return back()->withErrors([
                'error' => 'Failed to create WhatsApp instance in Baileys server.'
            ])->withInput();
        }

        try {
            $instance = Instance::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'label' => $request->label,
                'phone_number' => $request->phone_number,
                'access_token' => null,
                'status' => 'pending',
                'green_instance_id' => $instanceId,
                'green_api_token' => null,
            ]);

            Log::info('Instance created successfully', [
                'instance_id' => $instance->id,
                'baileys_id' => $instanceId
            ]);

            sleep(3);

            return redirect()
                ->route('instance.show', $instance->id)
                ->with('success', 'WhatsApp instance created! Scan the QR code.');
        } catch (\Exception $e) {
            Log::error('Database error: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Failed to save instance: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * ✅ عرض تفاصيل Instance
     */
    public function show($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        $stateData = $this->getInstanceState($instance); // ✅ now returns array
        $qrCode = null;

        if ($stateData['connected']) {
            if ($instance->status !== 'connected') {
                $instance->update([
                    'status' => 'connected',
                    'access_token' => Str::random(60),
                    // ✅ اختياري: خزّن الرقم اللي رجع من السيرفر لو فاضي
                    'phone_number' => $instance->phone_number ?: ($stateData['phone'] ?? null),
                ]);
            }
        } else {
            $instance->update(['status' => 'pending']);

            $qrCode = $this->getQrCodeWithRetry($instance, 3);

            Log::info('QR Code Result', [
                'instance_id' => $instance->id,
                'baileys_id' => $instance->green_instance_id,
                'has_qr' => !empty($qrCode),
                'qr_length' => $qrCode ? strlen($qrCode) : 0,
                'state' => $stateData['status'],
                'phone' => $stateData['phone'],
                'connected' => $stateData['connected'],
            ]);
        }

        // ✅ لو views بتعتمد على string state
        $state = $stateData['status'];

        $user = Auth::user();
        $customer = $user->customer;
        if ($customer) {
            $customer->updateTrialStatusIfExpired();
            $customer->refresh();
        }

        return view('whatsapp.instance', compact('instance', 'state', 'qrCode', 'customer'));
    }

    /**
     * ✅ فحص حالة Instance (AJAX)
     */
    public function checkStatus($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        $stateData = $this->getInstanceState($instance);

        if ($stateData['connected'] && $instance->status !== 'connected') {
            $instance->update([
                'status' => 'connected',
                'access_token' => Str::random(60),
                'phone_number' => $instance->phone_number ?: ($stateData['phone'] ?? null),
            ]);
        } elseif (!$stateData['connected']) {
            $instance->update([
                'status' => 'pending',
                'access_token' => null
            ]);
        }

        return response()->json([
            'status' => $instance->status,
            'access_token' => $instance->access_token,
            'state' => $stateData['status'],
            'phoneNumber' => $stateData['phone'],
            'connected' => $stateData['connected'],
        ]);
    }

    /**
     * ✅ إرسال رسالة
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $instance = Instance::where('user_id', $user->id)->findOrFail($id);

        if ($user->type === 'user' && $user->customer) {
            $user->customer->updateTrialStatusIfExpired();
        }

        // Check if customer subscription is blocked (but allow if trial is active)
        $blockedStatuses = ['cancelled', 'expired', 'pending'];
        if ($user->type === 'user' && $user->customer && in_array($user->customer->status, $blockedStatuses) && !$user->customer->hasActiveTrial()) {
            return response()->json(['error' => 'Your subscription is not active. Please renew your subscription to continue using this service.'], 403);
        }

        if ($instance->status !== 'connected') {
            return response()->json(['error' => 'Instance not connected'], 400);
        }

        try {
            $response = Http::timeout(30)->post(
                "{$this->baileysUrl}/api/instance/{$instance->green_instance_id}/send",
                [
                    'phone' => $request->phone,
                    'message' => $request->message,
                ]
            );

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Message sent']);
            }

            return response()->json(['error' => 'Failed to send message'], 500);
        } catch (\Exception $e) {
            Log::error('Send message failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Logout من Instance
     */
    public function logout($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        try {
            $response = Http::timeout(10)->post(
                "{$this->baileysUrl}/api/instance/{$instance->green_instance_id}/logout"
            );

            Log::info('Logout response', [
                'instance_id' => $instance->id,
                'success' => $response->successful()
            ]);

            sleep(1);
            $this->createBaileysInstance($instance->green_instance_id);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
        }

        $instance->update([
            'status' => 'pending',
            'access_token' => null
        ]);

        return redirect()
            ->route('instance.show', $instance->id)
            ->with('success', 'تم تسجيل الخروج – امسح QR لإعادة الاتصال');
    }

    /**
     * ✅ حذف Instance
     */
    public function destroy($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        try {
            Http::timeout(10)->delete(
                "{$this->baileysUrl}/api/instance/{$instance->green_instance_id}"
            );
        } catch (\Exception $e) {
            Log::error('Failed to delete Baileys instance: ' . $e->getMessage());
        }

        $instance->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Instance deleted successfully');
    }

    /**
     * ✅ Helper: تحديد إذا الحالة Connected
     */
    private function isConnectedByStatus(?string $status): bool
    {
        return in_array($status, ['connected', 'open', 'ready', 'authenticated'], true);
    }

    /**
     * ✅ Helper: الحصول على حالة Instance (مع phoneNumber و flag connected)
     */
    private function getInstanceState(Instance $instance): array
    {
        try {
            $response = Http::timeout(10)->get(
                "{$this->baileysUrl}/api/instance/{$instance->green_instance_id}/status"
            );

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Instance status payload', [
                    'instance' => $instance->green_instance_id,
                    'payload' => $data
                ]);

                $status = $data['status'] ?? 'pending';
                $phone = $data['phoneNumber'] ?? null;

                // ✅ أهم تعديل: لو phoneNumber موجودة يبقى اتربط حتى لو status pending
                $connected = !empty($phone) || $this->isConnectedByStatus($status);

                return [
                    'status' => $status,
                    'phone' => $phone,
                    'connected' => $connected,
                ];
            }

            return ['status' => 'pending', 'phone' => null, 'connected' => false];
        } catch (\Exception $e) {
            Log::error('Failed to get instance state: ' . $e->getMessage());
            return ['status' => 'pending', 'phone' => null, 'connected' => false];
        }
    }

    /**
     * ✅ Helper: الحصول على QR Code مع إعادة المحاولة
     */
    private function getQrCodeWithRetry(Instance $instance, $maxAttempts = 3)
    {
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            Log::info("QR Code attempt {$attempt}/{$maxAttempts}", [
                'instance_id' => $instance->id,
                'baileys_id' => $instance->green_instance_id
            ]);

            $qrCode = $this->getQrCode($instance);

            if ($qrCode) {
                Log::info("QR Code retrieved successfully on attempt {$attempt}");
                return $qrCode;
            }

            if ($attempt < $maxAttempts) {
                sleep($attempt * 2);
            }
        }

        Log::warning('Failed to get QR Code after all attempts', [
            'instance_id' => $instance->id,
            'attempts' => $maxAttempts
        ]);

        return null;
    }

    /**
     * ✅ Helper: الحصول على QR Code
     */
    private function getQrCode(Instance $instance)
    {
        try {
            $response = Http::timeout(15)->get(
                "{$this->baileysUrl}/api/instance/{$instance->green_instance_id}/qr"
            );

            Log::info('QR Code Response', [
                'instance_id' => $instance->id,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] && isset($data['qrCode'])) {
                    return $data['qrCode'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get QR code: ' . $e->getMessage());
            return null;
        }
    }
}
