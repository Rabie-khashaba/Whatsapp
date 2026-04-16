    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="me-2">
            <span class="fw-bold" data-en="Whatsapp" data-ar="واتساب">Whatsapp</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard')}}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-whatsapp"></i>
                    <span data-en="Whatsapp" data-ar="واتساب">Whatsapp</span>
                </a>
            </li>
            <li>
                <a href="friendly.html">
                    <i class="bi bi-chat-heart"></i>
                    <span data-en="Friendly" data-ar="الرسائل الودية">Friendly</span>
                </a>
            </li>
            <li>
                <a href="campaigns.html">
                    <i class="bi bi-megaphone"></i>
                    <span data-en="Campaigns" data-ar="الحملات">Campaigns</span>
                </a>
            </li>
            <li>
                <a href="data.html">
                    <i class="bi bi-database"></i>
                    <span data-en="Data" data-ar="البيانات">Data</span>
                </a>
            </li>
            <li>
                <a href="interactive-message.html">
                    <i class="bi bi-chat-square-text"></i>
                    <span data-en="Interactive Message" data-ar="الرسائل التفاعلية">Interactive Message</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="API & INTEGRATION" data-ar="API والدمج">API & INTEGRATION</span></li>
            
            <li>
                <a href="{{ route('api.integration.index') }}" class="{{ request()->routeIs('api.integration.*') ? 'active' : '' }}">
                    <i class="bi bi-code-square"></i>
                    <span data-en="WhatsApp Integration" data-ar="دمج الواتساب">WhatsApp Integration</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="MEDIA & FILES" data-ar="الوسائط والملفات">MEDIA & FILES</span></li>
            
            <li>
                <a href="media.html">
                    <i class="bi bi-images"></i>
                    <span data-en="Media" data-ar="الوسائط">Media</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="BILLING" data-ar="الفواتير">BILLING</span></li>
            
            <li>
                <a href="{{ route('subscriptions.index') }}" class="{{ request()->routeIs('subscriptions.index') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span data-en="Subscription" data-ar="الاشتراك">Subscription</span>
                </a>
            </li>
            <li>
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.index') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span data-en="Payment" data-ar="الدفع">Payment</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirmLogout(event)">
                <i class="bi bi-box-arrow-left me-2"></i>
                <span data-en="Logout" data-ar="تسجيل الخروج">Logout</span>
            </button>
        </form>
        </div>
    </aside>

