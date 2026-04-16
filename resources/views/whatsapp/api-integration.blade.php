@extends('layouts.master')

@section('title', 'API Integration - WhatsApp Campaign Platform')
@section('page-title', 'API Integration')
@section('page-title-ar', 'دمج الـ API')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Error:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="dashboard-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h4 class="mb-1">WhatsApp API Integration</h4>
            <p class="text-muted mb-0">Manage your API access, instances, and endpoint references from one place.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="#instance-endpoints" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-phone me-1"></i>Instances
            </a>
            <a href="#docs-reference" class="btn btn-primary btn-sm">
                <i class="bi bi-book me-1"></i>Docs
            </a>
        </div>
    </div>
</div>

{{-- <div class="dashboard-card mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="stats-icon primary">
            <i class="bi bi-link-45deg"></i>
        </div>
        <div class="flex-grow-1">
            <label class="form-label small fw-bold mb-1">Base URL</label>
            <div class="bg-light p-3 rounded d-flex justify-content-between align-items-center flex-wrap gap-2">
                <code id="apiBaseUrl">{{ $apiBaseUrl }}</code>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copyText('apiBaseUrl')">
                    <i class="bi bi-clipboard me-1"></i>Copy
                </button>
            </div>
        </div>
    </div>
</div> --}}

<div class="dashboard-card mb-4" id="instance-endpoints">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Connected Instances</h5>
        <span class="badge bg-primary">{{ $instances->count() }} Instances</span>
    </div>
    <p class="text-muted small">These instances are available under your customer account and can be referenced in the API documentation below.</p>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Unique ID</th>
                    <th>Status</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse($instances as $instance)
                    <tr>
                        <td class="fw-semibold">{{ $instance->name }}</td>
                        <td><code>{{ $instance->green_instance_id ?: $instance->id }}</code></td>
                        <td>
                            <span class="badge {{ $instance->status === 'connected' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ strtoupper($instance->status) }}
                            </span>
                        </td>
                        <td>{{ $instance->phone_number ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No instances available yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-card mb-4" id="docs-reference">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-info-circle fs-5 text-primary"></i>
        <h5 class="mb-0">Integration Notes</h5>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-2">1. Generate Token</h6>
                <p class="text-muted small mb-0">Create one active API token from this page before making any request.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-2">2. Keep URL Ready</h6>
                <p class="text-muted small mb-0">Use the base URL and the send-message endpoint exactly as shown below.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-2">3. Start Requests</h6>
                <p class="text-muted small mb-0">Send authenticated requests from your backend or automation platform.</p>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mt-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge bg-primary">POST</span>
        <h5 class="mb-0">Send Message</h5>
    </div>
    <p class="text-muted">Use the existing API endpoint below exactly as implemented in your backend. This section is documentation only and does not change any API code.</p>

    <h6 class="mt-4 mb-2">Endpoint</h6>
    <div class="bg-light p-3 rounded">
        <code>{{ rtrim((string) config('services.whatsapp_api.url', config('app.url')), '/') }}/api/send-message</code>
    </div>

    <h6 class="mt-4 mb-2">Laravel Route</h6>
    <div class="bg-dark text-white p-3 rounded">
        <pre class="mb-0"><code class="text-white">Route::post(
    '/send-message',
    [WhatsappController::class, 'sendMessage']
);</code></pre>
    </div>

    <h6 class="mt-4 mb-2">Request Headers</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>HEADER</th>
                    <th>REQUIRED</th>
                    <th>DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>Authorization</code></td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td><code>Bearer WHATSAPP_API_TOKEN</code> or the instance access token used by your API.</td>
                </tr>
                <tr>
                    <td><code>Content-Type</code></td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td><code>application/json</code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h6 class="mt-4 mb-2">Request Body</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>FIELD</th>
                    <th>REQUIRED</th>
                    <th>DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>phone</code></td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td>Destination phone number.</td>
                </tr>
                <tr>
                    <td><code>message</code></td>
                    <td><span class="badge bg-success">Yes</span></td>
                    <td>Message body to send.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h6 class="mt-4 mb-2">Example cURL</h6>
    <div class="bg-dark text-white p-3 rounded">
        <pre class="mb-0"><code class="text-white">curl -X POST {{ rtrim((string) config('services.whatsapp_api.url', config('app.url')), '/') }}/api/send-message \
  -H "Authorization: Bearer WHATSAPP_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "201001234567",
    "message": "Hello from Wasla API"
  }'</code></pre>
    </div>
</div>

<div class="dashboard-card mt-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-gear fs-5 text-primary"></i>
        <h5 class="mb-0">.env Setup</h5>
    </div>
    <p class="text-muted">In the external Laravel project that will consume this API, add these values to your <code>.env</code> file:</p>

    <div class="bg-dark text-white p-3 rounded">
        <pre class="mb-0"><code class="text-white"># whatsapp
WHATSAPP_API_TOKEN=YOUR_WHATSAPP_API_TOKEN
WHATSAPP_API_URL={{ rtrim((string) config('services.whatsapp_api.url', config('app.url')), '/') }}</code></pre>
    </div>
</div>

<div class="dashboard-card mt-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-file-code fs-5 text-primary"></i>
        <h5 class="mb-0">Service Example</h5>
    </div>
    <p class="text-muted">Use this service class in the external project to call the existing <code>/api/send-message</code> endpoint. This is only a usage example and does not modify your current API backend.</p>

    <div class="bg-dark text-white p-3 rounded">
<pre class="mb-0"><code class="text-white">namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    private $token;
    private $apiUrl;

    public function __construct()
    {
        $this->token = env('WHATSAPP_API_TOKEN');
        $this->apiUrl = env('WHATSAPP_API_URL');
    }

    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = ltrim($phone, '0');
        $phone = preg_replace('/^(20|\\+20)/', '', $phone);

        return '20' . $phone;
    }

    public function send($phone, $message)
    {
        $formattedPhone = $this->formatPhone($phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(90)
            ->retry(2, 100)
            ->post($this->apiUrl . '/api/send-message', [
                'phone' => $formattedPhone,
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', [
                    'phone' => $formattedPhone,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('WhatsApp send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $formattedPhone
            ]);

            return [
                'success' => false,
                'error' => 'فشل إرسال الرسالة'
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('WhatsApp Connection Error', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone
            ]);

            return [
                'success' => false,
                'error' => 'مشكلة في الاتصال بخدمة WhatsApp'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Unexpected Error', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone
            ]);

            return [
                'success' => false,
                'error' => 'حدث خطأ غير متوقع'
            ];
        }
    }
}</code></pre>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyText(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    const text = element.textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard');
    });
}
</script>
@endsection
