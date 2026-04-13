// ========================================
// Global Variables
// ========================================
let currentLanguage = 'en';
let otpTimer;
let timeLeft = 60;

// Flag to prevent multiple redirects
let isRedirecting = false;

// ========================================
// Auto Translation Dictionary
// ========================================
const translations = {
    // Common
    'Dashboard': 'لوحة التحكم',
    'Logout': 'تسجيل الخروج',
    'Profile': 'الملف الشخصي',
    'Settings': 'الإعدادات',
    'Back': 'رجوع',
    'Cancel': 'إلغاء',
    'Save': 'حفظ',
    'Edit': 'تعديل',
    'Delete': 'حذف',
    'View': 'عرض',
    'Search': 'بحث',
    'Filter': 'تصفية',
    'Export': 'تصدير',
    'Import': 'استيراد',
    'Download': 'تحميل',
    'Upload': 'رفع',
    'Print': 'طباعة',
    'Close': 'إغلاق',
    'Submit': 'إرسال',
    'Reset': 'إعادة تعيين',
    'Add': 'إضافة',
    'Update': 'تحديث',
    'Refresh': 'تحديث',
    'Actions': 'إجراءات',
    'Status': 'الحالة',
    'Date': 'التاريخ',
    'Total': 'الإجمالي',
    'All': 'الكل',
    'Active': 'نشط',
    'Inactive': 'غير نشط',
    'Pending': 'قيد الانتظار',
    'Completed': 'مكتمل',
    'Failed': 'فشل',
    'Success': 'نجح',

    // Admin Panel
    'Admin Panel': 'لوحة الإدارة',
    'Admin Users': 'المسؤولين',
    'System Settings': 'إعدادات النظام',
    'All Customers': 'جميع العملاء',
    'Customers': 'العملاء',
    'Customer': 'العميل',
    'Subscriptions': 'الاشتراكات',
    'Active Subscriptions': 'الاشتراكات النشطة',
    'Expiring Soon': 'قريبة الانتهاء',
    'Plans': 'الباقات',
    'Payments': 'المدفوعات',
    'Pending Payments': 'المدفوعات المعلقة',
    'All Payments': 'جميع المدفوعات',
    'Payment': 'الدفع',
    'Invoices': 'الفواتير',
    'Invoice': 'الفاتورة',
    'Reports': 'التقارير',
    'Analytics': 'التحليلات',

    // Customer Fields
    'Full Name': 'الاسم الكامل',
    'Email': 'البريد الإلكتروني',
    'Email Address': 'البريد الإلكتروني',
    'Phone': 'الهاتف',
    'Phone Number': 'رقم الهاتف',
    'Address': 'العنوان',
    'Country': 'البلد',
    'City': 'المدينة',

    // Plan & Subscription
    'Plan': 'الباقة',
    'Basic': 'أساسية',
    'Pro': 'احترافية',
    'Enterprise': 'مؤسسات',
    'Monthly': 'شهري',
    'Yearly': 'سنوي',
    'Billing Cycle': 'دورة الفوترة',
    'Subscription': 'الاشتراك',
    'Start Date': 'تاريخ البداية',
    'Expiry Date': 'تاريخ الانتهاء',
    'Expired': 'منتهي',

    // Payment Methods
    'Payment Method': 'طريقة الدفع',
    'Vodafone Cash': 'فودافون كاش',
    'Bank Transfer': 'تحويل بنكي',
    'Credit Card': 'بطاقة ائتمان',

    // Stats
    'Total Customers': 'إجمالي العملاء',
    'Total Revenue': 'الإيراد الإجمالي',
    'Monthly Revenue': 'الإيراد الشهري',
    'This Month': 'هذا الشهر',
    'Last Month': 'الشهر الماضي',

    // Messages
    'Loading...': 'جاري التحميل...',
    'No data found': 'لا توجد بيانات',
    'Are you sure?': 'هل أنت متأكد؟',
    'Success!': 'نجح!',
    'Error!': 'خطأ!',
    'Warning!': 'تحذير!'
};

function autoTranslate() {
    const currentLang = localStorage.getItem('selectedLanguage') || 'en';

    if (currentLang !== 'ar') return;

    // Translate all text nodes
    document.querySelectorAll('*').forEach(element => {
        // Skip script and style tags
        if (element.tagName === 'SCRIPT' || element.tagName === 'STYLE') return;

        // Translate direct text content
        Array.from(element.childNodes).forEach(node => {
            if (node.nodeType === 3) { // Text node
                const text = node.textContent.trim();
                if (text && translations[text]) {
                    node.textContent = node.textContent.replace(text, translations[text]);
                }
            }
        });

        // Translate placeholders
        if (element.placeholder && translations[element.placeholder]) {
            element.placeholder = translations[element.placeholder];
        }

        // Translate titles
        if (element.title && translations[element.title]) {
            element.title = translations[element.title];
        }

        // Translate button text
        if (element.tagName === 'BUTTON') {
            const text = element.textContent.trim();
            if (text && translations[text]) {
                element.innerHTML = element.innerHTML.replace(text, translations[text]);
            }
        }
    });
}
function toggleLanguage() {
    currentLanguage = currentLanguage === 'en' ? 'ar' : 'en';
    applyLanguage(currentLanguage);

    // Apply auto translation after language change
    setTimeout(() => {
        autoTranslate();
    }, 100);
}

function applyLanguage(lang) {
    const html = document.documentElement;
    const langText = document.getElementById('lang-text');

    if (lang === 'ar') {
        html.setAttribute('lang', 'ar');
        html.setAttribute('dir', 'rtl');
        if (langText) langText.textContent = 'English';
        updateContent('ar');
    } else {
        html.setAttribute('lang', 'en');
        html.setAttribute('dir', 'ltr');
        if (langText) langText.textContent = 'العربية';
        updateContent('en');
    }

    localStorage.setItem('selectedLanguage', lang);
}

function loadSavedLanguage() {
    const savedLang = localStorage.getItem('selectedLanguage');
    if (savedLang) {
        currentLanguage = savedLang;
        applyLanguage(savedLang);
    }
}

function updateContent(lang) {
    const elements = document.querySelectorAll('[data-en], [data-ar]');
    elements.forEach(element => {
        if (lang === 'ar' && element.hasAttribute('data-ar')) {
            element.textContent = element.getAttribute('data-ar');
        } else if (lang === 'en' && element.hasAttribute('data-en')) {
            element.textContent = element.getAttribute('data-en');
        }
    });

    const inputs = document.querySelectorAll('[data-en-placeholder], [data-ar-placeholder]');
    inputs.forEach(input => {
        if (lang === 'ar' && input.hasAttribute('data-ar-placeholder')) {
            input.placeholder = input.getAttribute('data-ar-placeholder');
        } else if (lang === 'en' && input.hasAttribute('data-en-placeholder')) {
            input.placeholder = input.getAttribute('data-en-placeholder');
        }
    });
}

// ========================================
// Check Authentication (FIXED)
// ========================================
function checkAuth() {
    // Prevent multiple checks
    if (isRedirecting) return;

    // Prevent infinite loop
    if (sessionStorage.getItem('redirecting')) {
        sessionStorage.removeItem('redirecting');
        return;
    }

    const authToken = sessionStorage.getItem('authToken');
    const adminToken = sessionStorage.getItem('adminToken');
    const isAdmin = sessionStorage.getItem('isAdmin');

    // Get filename more reliably (works on Windows and web servers)
    let fileName = window.location.pathname;
    if (fileName.includes('\\')) {
        // Windows path
        fileName = fileName.split('\\').pop();
    } else {
        // Unix/web path
        fileName = fileName.split('/').pop();
    }

    // Fallback to href if pathname is empty
    if (!fileName || fileName === '') {
        const href = window.location.href;
        fileName = href.substring(href.lastIndexOf('/') + 1).split('?')[0];
    }

    // Admin pages logic
    if (fileName.startsWith('admin-')) {
        if (fileName === 'admin-login.html') {
            // If on admin login page and already authenticated
            if (adminToken && isAdmin === 'true') {
                isRedirecting = true;
                sessionStorage.setItem('redirecting', 'true');
                window.location.replace('admin-dashboard.html');
            }
        } else {
            // On other admin pages, check if authenticated as admin
            if (!adminToken || isAdmin !== 'true') {
                isRedirecting = true;
                sessionStorage.setItem('redirecting', 'true');
                window.location.replace('admin-login.html');
            }
        }
        return;
    }

    // Customer pages logic
    const authPages = ['login.html', 'register.html', 'otp.html'];
    const isAuthPage = authPages.some(page => fileName === page || fileName.includes(page));

    if (authToken && isAuthPage) {
        isRedirecting = true;
        sessionStorage.setItem('redirecting', 'true');
        window.location.replace('dashboard.html');
        return;
    }

    if (!authToken && fileName === 'dashboard.html') {
        isRedirecting = true;
        sessionStorage.setItem('redirecting', 'true');
        window.location.replace('login.html');
        return;
    }
}

// ========================================
// Admin Authentication Check (FIXED)
// ========================================
function checkAdminAuth() {
    // Prevent multiple checks
    if (isRedirecting) return true;

    // Prevent infinite loop - check if we've already redirected
    if (sessionStorage.getItem('redirecting')) {
        sessionStorage.removeItem('redirecting');
        return true;
    }

    const adminToken = sessionStorage.getItem('adminToken');
    const isAdmin = sessionStorage.getItem('isAdmin');

    // Get filename more reliably (works on Windows and web servers)
    let fileName = window.location.pathname;
    if (fileName.includes('\\')) {
        // Windows path
        fileName = fileName.split('\\').pop();
    } else {
        // Unix/web path
        fileName = fileName.split('/').pop();
    }

    // Fallback to href if pathname is empty
    if (!fileName || fileName === '') {
        const href = window.location.href;
        fileName = href.substring(href.lastIndexOf('/') + 1).split('?')[0];
    }

    // If on admin login page and already authenticated
    if (fileName === 'admin-login.html') {
        if (adminToken && isAdmin === 'true') {
            isRedirecting = true;
            sessionStorage.setItem('redirecting', 'true');
            window.location.replace('admin-dashboard.html');
            return false;
        }
        return true;
    }

    // On other admin pages, check if authenticated
    if (fileName.startsWith('admin-') && fileName !== 'admin-login.html') {
        if (!adminToken || isAdmin !== 'true') {
            isRedirecting = true;
            sessionStorage.setItem('redirecting', 'true');
            window.location.replace('admin-login.html');
            return false;
        }
    }

    return true;
}

// ========================================
// Register Form Handler
// ========================================
function handleRegister(event) {
    event.preventDefault();

    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const countryCode = document.getElementById('countryCode').value;
    const phone = document.getElementById('phone').value;

    if (!fullName || !email || !phone) {
        showAlert('Please fill in all fields', 'danger');
        return;
    }

    const userData = {
        fullName,
        email,
        countryCode,
        phone,
        fullPhone: countryCode + phone
    };

    sessionStorage.setItem('pendingUser', JSON.stringify(userData));
    showAlert('Registration successful! Redirecting to OTP verification...', 'success');

    setTimeout(() => {
        window.location.href = 'otp.html';
    }, 1500);
}

// ========================================
// Login Form Handler
// ========================================
function handleLogin(event) {
    event.preventDefault();

    const countryCode = document.getElementById('loginCountryCode').value;
    const phone = document.getElementById('loginPhone').value;

    if (!phone) {
        showAlert('Please enter your phone number', 'danger');
        return;
    }

    const phoneData = {
        countryCode,
        phone,
        fullPhone: countryCode + phone
    };

    sessionStorage.setItem('loginPhone', JSON.stringify(phoneData));
    showAlert('OTP sent successfully!', 'success');

    setTimeout(() => {
        window.location.href = 'otp.html';
    }, 1500);
}

// ========================================
// OTP Form Handler
// ========================================
function handleOTP(event) {
    event.preventDefault();

    const otp1 = document.getElementById('otp1').value;
    const otp2 = document.getElementById('otp2').value;
    const otp3 = document.getElementById('otp3').value;
    const otp4 = document.getElementById('otp4').value;
    const otp5 = document.getElementById('otp5').value;
    const otp6 = document.getElementById('otp6').value;

    const otpCode = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;

    if (otpCode.length !== 6) {
        showAlert('Please enter complete OTP code', 'danger');
        return;
    }

    showAlert('OTP verified successfully! Redirecting to dashboard...', 'success');
    sessionStorage.setItem('authToken', 'demo-token-' + Date.now());

    setTimeout(() => {
        window.location.href = 'dashboard.html';
    }, 1500);
}

// ========================================
// Admin Login Handler
// ========================================
function handleAdminLogin(event) {
    event.preventDefault();

    const username = document.getElementById('adminUsername').value;
    const password = document.getElementById('adminPassword').value;

    if (!username || !password) {
        showAlert('Please enter username and password', 'danger');
        return;
    }

    const validAdmins = [
        { username: 'admin', password: 'admin123' },
        { username: 'superadmin', password: 'super123' }
    ];

    const admin = validAdmins.find(a => a.username === username && a.password === password);

    if (admin) {
        sessionStorage.setItem('adminToken', 'admin-token-' + Date.now());
        sessionStorage.setItem('adminUsername', username);
        sessionStorage.setItem('isAdmin', 'true');

        showAlert('Login successful!', 'success');

        setTimeout(() => {
            window.location.href = 'admin-dashboard.html';
        }, 1000);
    } else {
        showAlert('Invalid username or password', 'danger');
    }
}

// ========================================
// OTP Functions
// ========================================
function moveToNext(current, nextFieldId) {
    if (current.value.length === 1) {
        const nextField = document.getElementById(nextFieldId);
        if (nextField) nextField.focus();
    }
}

function moveToPrev(current, event, prevFieldId) {
    if (event.key === 'Backspace' && current.value === '') {
        const prevField = document.getElementById(prevFieldId);
        if (prevField) prevField.focus();
    }
}

function startOTPTimer() {
    const resendBtn = document.getElementById('resendBtn');
    const timerSpan = document.getElementById('timer');

    if (!resendBtn || !timerSpan) return;

    timeLeft = 60;
    // Make link unclickable and look disabled
    resendBtn.classList.add('disabled');
    resendBtn.style.pointerEvents = 'none';
    resendBtn.style.color = '#6c757d'; // muted color

    otpTimer = setInterval(() => {
        timeLeft--;
        timerSpan.textContent = ` (${timeLeft}s)`;

        if (timeLeft <= 0) {
            clearInterval(otpTimer);
            // Enable link
            resendBtn.classList.remove('disabled');
            resendBtn.style.pointerEvents = 'auto';
            resendBtn.style.color = ''; // reset color
            timerSpan.textContent = '';
        }
    }, 1000);
}

function resendOTP() {
    showAlert('OTP resent successfully!', 'success');

    for (let i = 1; i <= 6; i++) {
        const input = document.getElementById('otp' + i);
        if (input) input.value = '';
    }

    document.getElementById('otp1').focus();
    startOTPTimer();
}

function displayPhoneNumber() {
    const phoneDisplay = document.getElementById('phoneDisplay');

    if (phoneDisplay) {
        let phoneData;
        const pendingUser = sessionStorage.getItem('pendingUser');
        const loginPhone = sessionStorage.getItem('loginPhone');

        if (pendingUser) {
            phoneData = JSON.parse(pendingUser);
        } else if (loginPhone) {
            phoneData = JSON.parse(loginPhone);
        }

        if (phoneData) {
            phoneDisplay.textContent = phoneData.fullPhone;
        }
    }
}

// ========================================
// Logout Functions
// ========================================
function logout() {
    sessionStorage.removeItem('authToken');
    sessionStorage.removeItem('pendingUser');
    sessionStorage.removeItem('loginPhone');
    window.location.href = 'login.html';
}

function confirmLogout(event) {
    if (!confirm('Are you sure you want to logout?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
        sessionStorage.removeItem('adminUsername');
        sessionStorage.removeItem('isAdmin');
        window.location.href = 'admin-login.html';
    }
}

// ========================================
// Sidebar Toggle
// ========================================
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.toggle('show');

        if (!overlay) {
            const newOverlay = document.createElement('div');
            newOverlay.className = 'sidebar-overlay';
            newOverlay.onclick = toggleSidebar;
            document.body.appendChild(newOverlay);
            setTimeout(() => newOverlay.classList.add('show'), 10);
        } else {
            overlay.classList.toggle('show');
        }
    }
}

function closeSidebarOnClick() {
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    });
}

// ========================================
// Alert Function
// ========================================
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// ========================================
// Initialize on Page Load (FIXED - Final Version)
// ========================================
document.addEventListener('DOMContentLoaded', function () {
    // Clear redirecting flag after page loads (in case of successful redirect)
    setTimeout(() => {
        sessionStorage.removeItem('redirecting');
        isRedirecting = false;
    }, 500);

    // Load saved language first
    loadSavedLanguage();

    // Apply auto translation if Arabic
    setTimeout(() => {
        autoTranslate();
    }, 100);

    // Get current page filename more reliably
    let fileName = window.location.pathname;
    if (fileName.includes('\\')) {
        // Windows path
        fileName = fileName.split('\\').pop();
    } else {
        // Unix/web path
        fileName = fileName.split('/').pop();
    }

    // Fallback to href if pathname is empty
    if (!fileName || fileName === '') {
        const href = window.location.href;
        fileName = href.substring(href.lastIndexOf('/') + 1).split('?')[0];
    }

    if (!fileName) fileName = 'index.html';

    // Skip auth check for index/landing page
    if (fileName === 'index.html' || fileName === '') {
        return;
    }

    // Run auth check ONLY ONCE
    /*
    if (!window.authCheckRan) {
        window.authCheckRan = true;
        
        if (fileName.startsWith('admin-')) {
            checkAdminAuth();
        } else {
            checkAuth();
        }
    }
    */

    // Rest of initialization...
    // Rest of initialization...
    // Check if we are on the OTP page by looking for the timer element
    const timerElement = document.getElementById('timer');
    if (timerElement) {
        displayPhoneNumber();
        startOTPTimer();

        const firstInput = document.getElementById('otp1');
        if (firstInput) firstInput.focus();

        const otp6 = document.getElementById('otp6');
        if (otp6) {
            otp6.addEventListener('input', function () {
                if (this.value.length === 1) {
                    let allFilled = true;
                    for (let i = 1; i <= 6; i++) {
                        if (!document.getElementById('otp' + i).value) {
                            allFilled = false;
                            break;
                        }
                    }

                    if (allFilled) {
                        setTimeout(() => {
                            // Find the form and submit it
                            const form = this.closest('form');
                            if (form) form.dispatchEvent(new Event('submit'));
                        }, 500);
                    }
                }
            });
        }
    }

    // OTP paste handler
    const otpInputs = document.querySelectorAll('.otp-input');
    if (otpInputs.length > 0) {
        otpInputs[0].addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);

            for (let i = 0; i < digits.length; i++) {
                const input = document.getElementById('otp' + (i + 1));
                if (input) input.value = digits[i];
            }

            if (digits.length > 0) {
                const lastInput = document.getElementById('otp' + Math.min(digits.length, 6));
                if (lastInput) lastInput.focus();
            }
        });
    }

    // Only allow numbers in OTP inputs
    otpInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Phone number validation
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Close sidebar on link click
    closeSidebarOnClick();
});