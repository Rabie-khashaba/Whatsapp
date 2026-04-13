@extends('layouts.master')

@section('title', 'Instance Details - WhatsApp Campaign Platform')
@section('page-title', 'Instance Details')
@section('page-title-ar', 'تفاصيل المثيل')

@section('content')
    <!-- Instance Info Row -->
    <div class="row g-3 mb-4">
        
        {{-- QR Code Section --}}
        <div class="col-lg-6">
            <div class="dashboard-card text-center">
                <h5 class="mb-4" data-en="QR Code" data-ar="رمز الاستجابة السريعة">QR Code</h5>

                <div class="qr-container mb-3">
                    @if($instance->status === 'connected')
                        {{-- Connected State --}}
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-success"
                                data-en="Instance Connected"
                                data-ar="المثيل متصل">
                                Instance Connected
                            </h5>
                            <p class="text-muted"
                               data-en="WhatsApp Authorized Successfully"
                               data-ar="تم ربط واتساب بنجاح">
                                WhatsApp Authorized Successfully
                            </p>
                        </div>
                    @else
                        {{-- Pending State --}}
                        @if(!empty($qrCode))
                            {{-- QR Code Available --}}
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <img
                                    src="data:image/png;base64,{{ $qrCode }}"
                                    alt="QR Code"
                                    class="img-fluid mb-2"
                                    style="max-width:260px"
                                >
                                <p class="text-muted mt-2 text-center"
                                   data-en="Scan QR using WhatsApp"
                                   data-ar="امسح الرمز باستخدام واتساب">
                                    Scan QR using WhatsApp
                                </p>
                            </div>
                        @else
                            {{-- Loading State --}}
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted" data-en="Generating QR Code..." data-ar="جاري إنشاء رمز QR...">
                                    Generating QR Code...
                                </p>
                                <button onclick="location.reload()" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    <span data-en="Refresh" data-ar="تحديث">Refresh</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- STATUS BADGE --}}
                <span class="badge {{ $instance->status === 'connected' ? 'bg-success' : 'bg-warning' }} px-4 py-2">
                    <i class="bi {{ $instance->status === 'connected' ? 'bi-check-circle' : 'bi-clock-history' }} me-2"></i>
                    <span data-en="{{ strtoupper($instance->status) }}" data-ar="{{ $instance->status === 'connected' ? 'متصل' : 'جاري التحميل' }}">
                        {{ strtoupper($instance->status) }}
                    </span>
                </span>

                {{-- DEBUG INFO --}}
                <div class="mt-3 text-start bg-light p-2 rounded small">
                    <strong>🔍 Debug Info:</strong><br>
                    Instance ID: <code>{{ $instance->green_instance_id }}</code><br>
                    API Status: <code>{{ $state ?? 'N/A' }}</code><br>
                    Phone: <code>{{ $instance->phone_number ?? 'Not Set' }}</code><br>
                    
                    <button onclick="testBaileysConnection()" class="btn btn-sm btn-outline-primary mt-2 w-100">
                        <i class="bi bi-wifi me-1"></i>
                        <span data-en="Test Connection" data-ar="اختبار الاتصال">Test Connection</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Instance Actions --}}
        <div class="col-lg-6">
            <div class="dashboard-card">
                <h5 class="mb-3" data-en="Instance: {{ $instance->name }}" data-ar="المثيل: {{ $instance->name }}">
                    Instance: {{ $instance->name }}
                </h5>
                
                <div class="mb-3">
                    <span class="badge bg-secondary" data-en="Label" data-ar="التصنيف">Label</span>
                    <p class="mt-2 mb-0 fw-semibold" data-en="{{ $instance->label ?? 'N/A' }}" data-ar="{{ $instance->label ?? 'غير متوفر' }}">
                        {{ $instance->label ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-3">
                    <span class="badge bg-info text-dark" data-en="Access Token" data-ar="رمز الوصول">Access Token</span>
                    @if($instance->access_token)
                        <div class="input-group mt-2">
                            <input type="text" class="form-control" value="{{ $instance->access_token }}" readonly id="accessToken">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    @else
                        <p class="text-muted small mt-2">Scan QR Code to generate token.</p>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    @if($instance->status == 'connected')
                        <a href="{{ route('instance.logout', $instance->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to disconnect?');">
                            <i class="bi bi-power me-2"></i>
                            <span data-en="Logout / Disconnect" data-ar="تسجيل الخروج">Logout / Disconnect</span>
                        </a>
                        
                        <button class="btn btn-success" onclick="sendTestMessage()">
                            <i class="bi bi-send me-2"></i>
                            <span data-en="Send Test Message" data-ar="إرسال رسالة تجريبية">Send Test Message</span>
                        </button>
                    @endif
                    
                    <button class="btn btn-primary" onclick="refreshInfo()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        <span data-en="Refresh Info" data-ar="تحديث المعلومات">Refresh Info</span>
                    </button>
                    
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        <span data-en="Back to Dashboard" data-ar="العودة للوحة التحكم">Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
const instanceId = "{{ $instance->id }}";
const instanceStatus = "{{ $instance->status }}";
const baileysUrl = "{{ config('services.baileys.url') }}";
const greenInstanceId = "{{ $instance->green_instance_id }}";

// ✅ Test Baileys Connection
function testBaileysConnection() {
    fetch(baileysUrl + '/api/health')
        .then(res => res.json())
        .then(data => {
            alert('✅ Baileys Server is running!\n\n' + 
                  'Active Instances: ' + data.activeInstances + '\n' +
                  'Uptime: ' + Math.floor(data.uptime) + 's');
        })
        .catch(err => {
            alert('❌ Cannot connect to Baileys!\n\n' + 
                  'Error: ' + err.message + '\n\n' +
                  'Make sure Baileys is running at:\n' + baileysUrl);
        });
}

// ✅ Copy Access Token
function copyToken() {
    const token = document.getElementById('accessToken');
    token.select();
    document.execCommand('copy');
    alert('✅ Token copied to clipboard!');
}

// ✅ Send Test Message
function sendTestMessage() {
    const phone = prompt('Enter phone number (e.g., 201234567890):');
    if (!phone) return;
    
    const message = prompt('Enter message:', 'Test message from WhatsApp Platform!');
    if (!message) return;
    
    fetch('/instance/' + instanceId + '/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            phone: phone,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Message sent successfully!');
        } else {
            alert('❌ Failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('❌ Error: ' + err.message);
    });
}

// ✅ Refresh Page
function refreshInfo() {
    location.reload();
}

// ✅ Auto-refresh للحصول على QR إذا كان missing
@if($instance->status === 'pending' && empty($qrCode))
let retryCount = 0;
const maxRetries = 5;

function fetchQR() {
    if (retryCount >= maxRetries) {
        console.log('Max retries reached');
        alert("Failed to generate QR code after multiple attempts. Please try again later.");
        return;
    }
    
    console.log(`Fetching QR... Attempt ${retryCount + 1}`);
    
    fetch(`${baileysUrl}/api/instance/${greenInstanceId}/qr`)
        .then(res => res.json())
        .then(data => {
            console.log('QR Response:', data);
            
            if (data.success && data.qrCode) {
                location.reload();
            } else {
                retryCount++;
                setTimeout(fetchQR, 3000);
            }
        })
        .catch(err => {
            console.error('Error fetching QR:', err);
            retryCount++;
            setTimeout(fetchQR, 3000);
        });
}

setTimeout(fetchQR, 2000);
@endif

// ✅ Auto-check للـ Status
@if($instance->status === 'pending')
setInterval(() => {
    fetch(`/instance/${instanceId}/check`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'connected') {
                location.reload();
            }
        })
        .catch(err => console.error('Status check failed:', err));
}, 5000);
@endif
</script>
@endsection
