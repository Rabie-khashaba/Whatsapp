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
                    <span data-en="{{ strtoupper($instance->status) }}" data-ar="{{ $instance->status === 'connected' ? 'متصل' : 'قيد التحميل' }}">
                        {{ strtoupper($instance->status) }}
                    </span>
                </span>

                {{-- DEBUG INFO --}}
                <div class="mt-3 text-start bg-light p-2 rounded small">
                    <strong data-en="🔍 Debug Info:" data-ar="🔍 معلومات التصحيح:">🔍 Debug Info:</strong><br>
                    <span data-en="Instance ID:" data-ar="معرف المثيل:">Instance ID:</span> <code>{{ $instance->green_instance_id }}</code><br>
                    <span data-en="API Status:" data-ar="حالة API:">API Status:</span> <code>{{ $state ?? 'N/A' }}</code><br>
                    <span data-en="Phone:" data-ar="الهاتف:">Phone:</span> <code>{{ $instance->phone_number ?? $isBlocked ? 'Not Set' : 'Not Set' }}</code><br>

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
                    <span class="badge bg-secondary" data-en="Label" data-ar="التسمية">Label</span>
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
                        <p class="text-muted small mt-2" data-en="Scan QR Code to generate token." data-ar="امسح رمز QR لتوليد رمز الوصول.">Scan QR Code to generate token.</p>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    @if($instance->status == 'connected')
                        @php
                            $blockedStatuses = ['cancelled', 'expired', 'pending'];
                            $isBlocked = isset($customer) && in_array($customer->status, $blockedStatuses);
                        @endphp

                        <a href="{{ route('instance.logout', $instance->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to disconnect?');">
                            <i class="bi bi-power me-2"></i>
                            <span data-en="Logout / Disconnect" data-ar="تسجيل الخروج">Logout / Disconnect</span>
                        </a>

                        <button class="btn btn-success" onclick="sendTestMessage()" {{ $isBlocked ? 'disabled' : '' }}>
                            <i class="bi bi-send me-2"></i>
                            <span data-en="Send Test Message" data-ar="إرسال رسالة اختبار">Send Test Message</span>
                        </button>
                        @if($isBlocked)
                            <div class="alert alert-warning mt-2">
                                <small>Your subscription is not active. Please renew your subscription to continue using this service.</small>
                            </div>
                        @endif
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

@section('modals')
<div class="modal fade" id="subscriptionExpiredModal" tabindex="-1" aria-labelledby="subscriptionExpiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriptionExpiredModalLabel" data-en="Subscription Expired" data-ar="انتهت صلاحية الاشتراك">Subscription Expired</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-warning fs-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <p class="mb-2 fw-semibold" data-en="Sending is currently disabled." data-ar="الإرسال معطل حالياً.">Sending is currently disabled.</p>
                        <p class="mb-0 text-muted" id="subscriptionExpiredMessage" data-en="Your subscription has expired. Please renew it, then try again." data-ar="انتهت صلاحية اشتراكك. يرجى تجديده ثم المحاولة مرة أخرى.">Your subscription has expired. Please renew it, then try again.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-en="Close" data-ar="إغلاق">Close</button>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary" id="subscriptionExpiredRenewLink">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    <span data-en="Renew Now" data-ar="تجديد الآن">Renew Now</span>
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

function showSubscriptionExpiredModal(message, renewUrl = "{{ route('subscriptions.index') }}") {
    const modalElement = document.getElementById('subscriptionExpiredModal');
    const messageElement = document.getElementById('subscriptionExpiredMessage');
    const renewLinkElement = document.getElementById('subscriptionExpiredRenewLink');

    if (messageElement) {
        messageElement.textContent = message || 'Your subscription has expired. Please renew it, then try again.';
    }

    if (renewLinkElement) {
        renewLinkElement.href = renewUrl || "{{ route('subscriptions.index') }}";
    }

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function testBaileysConnection() {
    const lang = document.documentElement.lang || 'en';
    fetch(baileysUrl + '/api/health')
        .then(res => res.json())
        .then(data => {
            const successMsg = lang === 'ar'
                ? 'خادم Baileys يعمل!\n\nالمثيلات النشطة: ' + data.activeInstances + '\nوقت التشغيل: ' + Math.floor(data.uptime) + 's'
                : 'Baileys Server is running!\n\nActive Instances: ' + data.activeInstances + '\nUptime: ' + Math.floor(data.uptime) + 's';
            alert(successMsg);
        })
        .catch(err => {
            const errorMsg = lang === 'ar'
                ? 'لا يمكن الاتصال بـ Baileys!\n\nالخطأ: ' + err.message + '\n\nتأكد من أن Baileys يعمل على:\n' + baileysUrl
                : 'Cannot connect to Baileys!\n\nError: ' + err.message + '\n\nMake sure Baileys is running at:\n' + baileysUrl;
            alert(errorMsg);
        });
}

function copyToken() {
    const token = document.getElementById('accessToken');
    token.select();
    document.execCommand('copy');
    const lang = document.documentElement.lang || 'en';
    const message = lang === 'ar' ? 'تم نسخ الرمز إلى الحافظة!' : 'Token copied to clipboard!';
    alert(message);
}

function sendTestMessage() {
    const lang = document.documentElement.lang || 'en';
    const phonePrompt = lang === 'ar' ? 'أدخل رقم الهاتف (مثال: 201234567890):' : 'Enter phone number (e.g., 201234567890):';
    const phone = prompt(phonePrompt);
    if (!phone) return;

    const messagePrompt = lang === 'ar' ? 'أدخل الرسالة:' : 'Enter message:';
    const defaultMsg = lang === 'ar' ? 'رسالة اختبار من منصة WhatsApp!' : 'Test message from WhatsApp Platform!';
    const message = prompt(messagePrompt, defaultMsg);
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
            const lang = document.documentElement.lang || 'en';
            if (data.success) {
                const successMsg = lang === 'ar' ? 'تم إرسال الرسالة بنجاح!' : 'Message sent successfully!';
                alert(successMsg);
                return;
            }

            if (data.renew_url) {
                showSubscriptionExpiredModal(data.error, data.renew_url);
                return;
            }

            const errorPrefix = lang === 'ar' ? 'فشل: ' : 'Failed: ';
            const unknownError = lang === 'ar' ? 'خطأ غير معروف' : 'Unknown error';
            alert(errorPrefix + (data.error || unknownError));
        })
        .catch(err => {
            const lang = document.documentElement.lang || 'en';
            const errorMsg = lang === 'ar' ? 'خطأ: ' + err.message : 'Error: ' + err.message;
            alert(errorMsg);
        });
}

function refreshInfo() {
    location.reload();
}

@if($instance->status === 'pending' && empty($qrCode))
let retryCount = 0;
const maxRetries = 5;

function fetchQR() {
    const lang = document.documentElement.lang || 'en';
    if (retryCount >= maxRetries) {
        console.log('Max retries reached');
        const failMsg = lang === 'ar' ? 'فشل في توليد رمز QR بعد محاولات متعددة. يرجى المحاولة لاحقاً.' : 'Failed to generate QR code after multiple attempts. Please try again later.';
        alert(failMsg);
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
