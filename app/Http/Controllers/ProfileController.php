<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InstanceController extends Controller
{
    public function index()
    {
        $instances = Instance::where('user_id', Auth::id())->latest()->get();
        return view('whatsapp.dashboard', compact('instances'));
    }

    /**
     * ✅ إنشاء Instance جديد في Green API تلقائيًا
     */
    private function createGreenApiInstance()
    {
        try {
            $response = Http::timeout(30)->post('https://api.green-api.com/partner/createInstance', [
                'partnerToken' => config('services.green_api.partner_token'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'instanceId' => $data['idInstance'] ?? null,
                    'apiToken' => $data['apiTokenInstance'] ?? null,
                ];
            }

            Log::error('Green API Instance Creation Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Green API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ حفظ Instance جديد (مع إنشاء تلقائي في Green API)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
        ]);

        // ✅ إنشاء Instance جديد في Green API
        $greenData = $this->createGreenApiInstance();
        
        if (!$greenData || !$greenData['instanceId'] || !$greenData['apiToken']) {
            return back()->withErrors([
                'msg' => 'Failed to create WhatsApp instance. Please check your Partner Token or contact support.'
            ]);
        }

        // ✅ حفظ Instance في قاعدة البيانات
        $instance = Instance::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'label' => $request->label,
            'access_token' => null,
            'status' => 'pending',
            'green_instance_id' => $greenData['instanceId'],
            'green_api_token' => $greenData['apiToken'],
        ]);

        return redirect()
            ->route('instance.show', $instance->id)
            ->with('success', 'WhatsApp instance created successfully! Scan QR code to connect.');
    }

    /**
     * ✅ Logout من Instance
     */
    public function logout($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        try {
            Http::timeout(10)->get(
                rtrim(config('services.green_api.host'), '/') .
                "/waInstance{$instance->green_instance_id}/logout/{$instance->green_api_token}"
            );
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
        }

        $instance->update([
            'status' => 'pending',
            'access_token' => null
        ]);

        return back()->with('success', 'Instance disconnected');
    }

    /**
     * ✅ الحصول على حالة Instance
     */
    private function getInstanceState(Instance $instance)
    {
        try {
            $response = Http::timeout(10)->get(
                rtrim(config('services.green_api.host'), '/') .
                "/waInstance{$instance->green_instance_id}/getStateInstance/{$instance->green_api_token}"
            );

            return $response->json('stateInstance');
        } catch (\Exception $e) {
            Log::error('Failed to get instance state: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ الحصول على QR Code
     */
    private function getQrCode(Instance $instance)
    {
        try {
            $response = Http::timeout(10)->get(
                rtrim(config('services.green_api.host'), '/') .
                "/waInstance{$instance->green_instance_id}/qr/{$instance->green_api_token}"
            );

            if ($response->successful() && $response->json('type') === 'qrCode') {
                return $response->json('message'); // Base64
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ عرض تفاصيل Instance
     */
    public function show($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        $state = $this->getInstanceState($instance);

        if ($state === 'authorized') {
            // ✅ Instance متصل
            if ($instance->status !== 'connected') {
                $instance->update([
                    'status' => 'connected',
                    'access_token' => Str::random(60)
                ]);
            }
            $qrCode = null;
        } else {
            // ✅ محتاج Scan
            $instance->update(['status' => 'pending']);
            $qrCode = $this->getQrCode($instance);
        }

        return view('whatsapp.instance', compact('instance', 'state', 'qrCode'));
    }

    /**
     * ✅ فحص حالة Instance (AJAX)
     */
    public function checkStatus($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        $state = $this->getInstanceState($instance);

        if ($state === 'authorized' && $instance->status !== 'connected') {
            $instance->update([
                'status' => 'connected',
                'access_token' => Str::random(60)
            ]);
        } elseif ($state !== 'authorized') {
            $instance->update([
                'status' => 'pending',
                'access_token' => null
            ]);
        }

        return response()->json([
            'status' => $instance->status,
            'access_token' => $instance->access_token
        ]);
    }

    /**
     * ✅ حذف Instance (من DB + Green API)
     */
    public function destroy($id)
    {
        $instance = Instance::where('user_id', Auth::id())->findOrFail($id);

        // ✅ محاولة حذف Instance من Green API
        try {
            Http::timeout(10)->post('https://api.green-api.com/partner/deleteInstanceAccount', [
                'partnerToken' => config('services.green_api.partner_token'),
                'idInstance' => $instance->green_instance_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete Green API instance: ' . $e->getMessage());
        }

        // ✅ حذف من Database
        $instance->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Instance deleted successfully.');
    }
}