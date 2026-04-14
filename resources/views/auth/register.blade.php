<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WhatsApp Campaign Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-page">
    <div class="language-switcher">
        <button class="btn btn-sm btn-outline-secondary" onclick="toggleLanguage()">
            <i class="bi bi-globe"></i> <span id="lang-text">العربية</span>
        </button>
    </div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="logo">
                        <h2 class="mt-3 mb-1" data-en="Create Account" data-ar="إنشاء حساب">Create Account</h2>
                        <p class="text-muted" data-en="Start your WhatsApp campaign journey"
                            data-ar="ابدأ رحلتك في حملات الواتساب">Start your WhatsApp campaign journey</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" id="registerForm">
                        @csrf

                        <div class="mb-3">
                            <label for="fullName" class="form-label" data-en="Full Name" data-ar="الاسم الكامل">Full
                                Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="fullName" name="name" required
                                    value="{{ old('name') }}" data-en-placeholder="Enter your full name"
                                    data-ar-placeholder="أدخل اسمك الكامل" placeholder="Enter your full name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label" data-en="Email Address"
                                data-ar="البريد الإلكتروني">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="{{ old('email') }}" data-en-placeholder="Enter your email"
                                    data-ar-placeholder="أدخل بريدك الإلكتروني" placeholder="Enter your email">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label" data-en="Phone Number" data-ar="رقم الهاتف">Phone
                                Number</label>
                            <div class="input-group">
                                <select class="form-select country-code" id="countryCode" name="country_code"
                                    style="max-width: 120px;">
                                    <option value="+20" @selected(old('country_code', '+20') === '+20')>EG +20</option>
                                    <option value="+966" @selected(old('country_code') === '+966')>SA +966</option>
                                    <option value="+971" @selected(old('country_code') === '+971')>AE +971</option>
                                    <option value="+965" @selected(old('country_code') === '+965')>KW +965</option>
                                    <option value="+974" @selected(old('country_code') === '+974')>QA +974</option>
                                    <option value="+968" @selected(old('country_code') === '+968')>OM +968</option>
                                    <option value="+973" @selected(old('country_code') === '+973')>BH +973</option>
                                    <option value="+962" @selected(old('country_code') === '+962')>JO +962</option>
                                    <option value="+961" @selected(old('country_code') === '+961')>LB +961</option>
                                    <option value="+212" @selected(old('country_code') === '+212')>MA +212</option>
                                    <option value="+213" @selected(old('country_code') === '+213')>DZ +213</option>
                                    <option value="+216" @selected(old('country_code') === '+216')>TN +216</option>
                                    <option value="+1" @selected(old('country_code') === '+1')>US +1</option>
                                    <option value="+44" @selected(old('country_code') === '+44')>UK +44</option>
                                </select>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                    value="{{ old('phone') }}" data-en-placeholder="Phone number"
                                    data-ar-placeholder="رقم الهاتف" placeholder="Phone number">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label" data-en="Password" data-ar="كلمة المرور">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required
                                    minlength="6" autocomplete="new-password"
                                    data-en-placeholder="Enter your password"
                                    data-ar-placeholder="أدخل كلمة المرور" placeholder="Enter your password">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label" data-en="Confirm Password"
                                data-ar="تأكيد كلمة المرور">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required minlength="6" autocomplete="new-password"
                                    data-en-placeholder="Confirm your password"
                                    data-ar-placeholder="أكد كلمة المرور" placeholder="Confirm your password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <span data-en="Create Account" data-ar="إنشاء الحساب">Create Account</span>
                        </button>

                        <div class="text-center">
                            <span class="text-muted" data-en="Already have an account?"
                                data-ar="لديك حساب بالفعل؟">Already have an account?</span>
                            <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold"
                                data-en="Login" data-ar="تسجيل الدخول">Login</a>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-4 text-muted small">
                    <p data-en="© 2024 WhatsApp Campaign Platform. All rights reserved."
                        data-ar="© 2024 منصة حملات الواتساب. جميع الحقوق محفوظة.">© 2024 WhatsApp Campaign Platform. All
                        rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
