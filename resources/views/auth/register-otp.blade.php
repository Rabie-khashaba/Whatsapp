<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration OTP - WhatsApp Campaign Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="logo">
                        <div class="otp-icon my-3">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <h2 class="mt-3 mb-1" data-en="Complete Registration" data-ar="إكمال التسجيل">Complete
                            Registration</h2>
                        <p class="text-muted" data-en="Enter the verification code sent to"
                            data-ar="أدخل رمز التحقق المرسل إلى">Enter the verification code sent to</p>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <p class="fw-semibold text-primary mb-0" id="phoneDisplay" dir="ltr">{{ $phone }}</p>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-outline-secondary p-1 lh-1"
                                style="font-size: 10px;" title="Edit Phone">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('verify.otp.register') }}" onsubmit="combineOtp()">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-center w-100" data-en="Verification Code"
                                data-ar="رمز التحقق">Verification Code</label>
                            <input type="hidden" name="otp" id="otp">
                            <div class="otp-inputs d-flex justify-content-center gap-2">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp1"
                                    onkeyup="moveToNext(this, 'otp2')" onkeydown="moveToPrev(this, event)">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp2"
                                    onkeyup="moveToNext(this, 'otp3')" onkeydown="moveToPrev(this, event, 'otp1')">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp3"
                                    onkeyup="moveToNext(this, 'otp4')" onkeydown="moveToPrev(this, event, 'otp2')">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp4"
                                    onkeyup="moveToNext(this, 'otp5')" onkeydown="moveToPrev(this, event, 'otp3')">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp5"
                                    onkeyup="moveToNext(this, 'otp6')" onkeydown="moveToPrev(this, event, 'otp4')">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" id="otp6"
                                    onkeydown="moveToPrev(this, event, 'otp5')">
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-muted mb-2">
                                <span data-en="Didn't receive the code?" data-ar="لم تستلم الرمز؟">Didn't receive the
                                    code?</span>
                            </p>
                            <a href="{{ route('resend.otp.register') }}"
                                class="btn btn-link text-decoration-none p-0 disabled" id="resendBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                <span data-en="Resend Code" data-ar="إعادة إرسال الرمز">Resend Code</span>
                                <span id="timer"> (60s)</span>
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <span data-en="Verify & Create Account" data-ar="تحقق وإنشاء الحساب">Verify & Create
                                Account</span>
                        </button>

                        <div class="text-center">
                            <a href="{{ route('register') }}" class="text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>
                                <span data-en="Back to Register" data-ar="العودة للتسجيل">Back to Register</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function moveToNext(current, nextId) {
            if (current.value.length === 1 && nextId) {
                document.getElementById(nextId).focus();
            }
        }

        function moveToPrev(current, event, prevId = null) {
            if (event.key === 'Backspace' && current.value === '' && prevId) {
                document.getElementById(prevId).focus();
            }
        }

        function combineOtp() {
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += document.getElementById('otp' + i).value;
            }
            document.getElementById('otp').value = otp;
        }

        let countdown = 60;
        const resendBtn = document.getElementById('resendBtn');
        const timerSpan = document.getElementById('timer');

        const timer = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(timer);
                resendBtn.classList.remove('disabled');
                timerSpan.textContent = '';
            } else {
                timerSpan.textContent = ` (${countdown}s)`;
            }
        }, 1000);
    </script>
</body>

</html>