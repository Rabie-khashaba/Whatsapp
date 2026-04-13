<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <script>sessionStorage.setItem('adminToken','server-session');sessionStorage.setItem('isAdmin','true');</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - Admin Panel</title>
    <base href="/" />
    <link rel="icon" type="image/png" href="images/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="images/logo.png" alt="Logo" class="me-2">
            <span class="fw-bold text-danger" data-en="Admin Panel" data-ar="Ù„ÙˆØ­Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©">Admin Panel</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="admin-dashboard.html">
                    <i class="bi bi-speedometer2"></i>
                    <span data-en="Dashboard" data-ar="Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠØ©">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="CUSTOMERS" data-ar="Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡">CUSTOMERS</span></li>
            
            <li>
                <a href="admin-customers.html" class="active">
                    <i class="bi bi-people"></i>
                    <span data-en="All Customers" data-ar="Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡">All Customers</span>
                </a>
            </li>
            <li>
                <a href="admin-customers.html?status=active">
                    <i class="bi bi-person-check"></i>
                    <span data-en="Active" data-ar="Ø§Ù„Ù†Ø´Ø·ÙŠÙ†">Active</span>
                </a>
            </li>
            <li>
                <a href="admin-customers.html?status=expired">
                    <i class="bi bi-person-x"></i>
                    <span data-en="Expired" data-ar="Ø§Ù„Ù…Ù†ØªÙ‡ÙŠ">Expired</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="SUBSCRIPTIONS" data-ar="Ø§Ù„Ø§Ø´ØªØ±Ø§ÙƒØ§Øª">SUBSCRIPTIONS</span></li>
            
            <li>
                <a href="admin-plans.html">
                    <i class="bi bi-boxes"></i>
                    <span data-en="Plans" data-ar="Ø§Ù„Ø¨Ø§Ù‚Ø§Øª">Plans</span>
                </a>
            </li>
            <li>
                <a href="admin-subscriptions.html">
                    <i class="bi bi-calendar-check"></i>
                    <span data-en="Active Subscriptions" data-ar="Ø§Ù„Ø§Ø´ØªØ±Ø§ÙƒØ§Øª Ø§Ù„Ù†Ø´Ø·Ø©">Active Subscriptions</span>
                </a>
            </li>
            <li>
                <a href="admin-subscriptions.html?status=expiring">
                    <i class="bi bi-calendar-x"></i>
                    <span data-en="Expiring Soon" data-ar="Ù‚Ø±ÙŠØ¨Ø© Ø§Ù„Ø§Ù†ØªÙ‡Ø§Ø¡">Expiring Soon</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="PAYMENTS" data-ar="Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª">PAYMENTS</span></li>
            
            <li>
                <a href="admin-payments-queue.html">
                    <i class="bi bi-hourglass-split"></i>
                    <span data-en="Pending Payments" data-ar="Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª Ø§Ù„Ù…Ø¹Ù„Ù‚Ø©">Pending Payments</span>
                </a>
            </li>
            <li>
                <a href="admin-payments.html">
                    <i class="bi bi-credit-card"></i>
                    <span data-en="All Payments" data-ar="Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª">All Payments</span>
                </a>
            </li>
            <li>
                <a href="admin-invoices.html">
                    <i class="bi bi-receipt"></i>
                    <span data-en="Invoices" data-ar="Ø§Ù„ÙÙˆØ§ØªÙŠØ±">Invoices</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="REPORTS" data-ar="Ø§Ù„ØªÙ‚Ø§Ø±ÙŠØ±">REPORTS</span></li>
            
            <li>
                <a href="admin-reports.html">
                    <i class="bi bi-graph-up"></i>
                    <span data-en="Analytics" data-ar="Ø§Ù„ØªØ­Ù„ÙŠÙ„Ø§Øª">Analytics</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span data-en="SETTINGS" data-ar="Ø§Ù„Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª">SETTINGS</span></li>
            
            <li>
                <a href="admin-admins.html">
                    <i class="bi bi-shield-check"></i>
                    <span data-en="Admin Users" data-ar="Ø§Ù„Ù…Ø³Ø¤ÙˆÙ„ÙŠÙ†">Admin Users</span>
                </a>
            </li>
            <li>
                <a href="admin-settings.html">
                    <i class="bi bi-gear"></i>
                    <span data-en="System Settings" data-ar="Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø§Ù„Ù†Ø¸Ø§Ù…">System Settings</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <button class="btn btn-outline-danger w-100" onclick="adminLogout()">
                <i class="bi bi-box-arrow-left me-2"></i>
                <span data-en="Logout" data-ar="ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬">Logout</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link mobile-menu-toggle d-lg-none p-0 text-dark" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <a href="admin-customers.html" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        <span data-en="Back" data-ar="Ø±Ø¬ÙˆØ¹">Back</span>
                    </a>
                    <div>
                        <h4 class="mb-0" data-en="Customer Details" data-ar="ØªÙØ§ØµÙŠÙ„ Ø§Ù„Ø¹Ù…ÙŠÙ„">Customer Details</h4>
                        <small class="text-muted" id="customerNameHeader">Loading...</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleLanguage()">
                        <i class="bi bi-globe"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=Admin&background=dc3545&color=fff" alt="Admin" class="rounded-circle" width="35" height="35">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="admin-profile.html"><i class="bi bi-person me-2"></i><span data-en="Profile" data-ar="Ø§Ù„Ù…Ù„Ù Ø§Ù„Ø´Ø®ØµÙŠ">Profile</span></a></li>
                            <li><a class="dropdown-item" href="admin-settings.html"><i class="bi bi-gear me-2"></i><span data-en="Settings" data-ar="Ø§Ù„Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª">Settings</span></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="adminLogout()"><i class="bi bi-box-arrow-left me-2"></i><span data-en="Logout" data-ar="ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø®Ø±ÙˆØ¬">Logout</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info Card -->
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="dashboard-card text-center">
                    <img src="" alt="Customer" class="rounded-circle mb-3" width="100" height="100" id="customerAvatar">
                    <h4 class="mb-1" id="customerName">Loading...</h4>
                    <p class="text-muted mb-3" id="customerEmail">loading@example.com</p>
                    <span class="badge bg-success mb-3" id="customerStatus">Active</span>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="editCustomerInfo()">
                            <i class="bi bi-pencil me-2"></i>
                            <span data-en="Edit Customer" data-ar="ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„Ø¹Ù…ÙŠÙ„">Edit Customer</span>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteCustomerAccount()">
                            <i class="bi bi-trash me-2"></i>
                            <span data-en="Delete Account" data-ar="Ø­Ø°Ù Ø§Ù„Ø­Ø³Ø§Ø¨">Delete Account</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <h5 class="mb-3" data-en="Customer Information" data-ar="Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ø¹Ù…ÙŠÙ„">Customer Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Phone Number" data-ar="Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ">Phone Number</label>
                            <p class="mb-0 fw-semibold" id="customerPhone">+20 100 123 4567</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Join Date" data-ar="ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù†Ø¶Ù…Ø§Ù…">Join Date</label>
                            <p class="mb-0 fw-semibold" id="customerJoinDate">Jan 15, 2024</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Current Plan" data-ar="Ø§Ù„Ø¨Ø§Ù‚Ø© Ø§Ù„Ø­Ø§Ù„ÙŠØ©">Current Plan</label>
                            <p class="mb-0"><span class="badge bg-primary" id="customerPlan">PRO</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Billing Cycle" data-ar="Ø¯ÙˆØ±Ø© Ø§Ù„ÙÙˆØªØ±Ø©">Billing Cycle</label>
                            <p class="mb-0 fw-semibold" id="customerBillingCycle">Monthly</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Subscription Date" data-ar="ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø´ØªØ±Ø§Ùƒ">Subscription Date</label>
                            <p class="mb-0 fw-semibold" id="customerSubDate">Jan 15, 2024</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Expiry Date" data-ar="ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ù†ØªÙ‡Ø§Ø¡">Expiry Date</label>
                            <p class="mb-0 fw-semibold" id="customerExpiryDate">Jan 15, 2025</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Instances" data-ar="Ø§Ù„Ù…Ø«ÙŠÙ„Ø§Øª">Instances</label>
                            <p class="mb-0 fw-semibold"><span id="currentInstances">3</span> / <span id="maxInstances">5</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small" data-en="Total Paid" data-ar="Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª">Total Paid</label>
                            <p class="mb-0 fw-semibold text-success" id="totalPaid">$299.00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-icon" style="background: rgba(37, 211, 102, 0.1); color: #25D366;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1" data-en="Active Instances" data-ar="Ø§Ù„Ù…Ø«ÙŠÙ„Ø§Øª Ø§Ù„Ù†Ø´Ø·Ø©">Active Instances</p>
                        <h3 class="mb-0 fw-bold" id="activeInstances">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1" data-en="Total Messages" data-ar="Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø±Ø³Ø§Ø¦Ù„">Total Messages</p>
                        <h3 class="mb-0 fw-bold" id="totalMessages">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1" data-en="Campaigns" data-ar="Ø§Ù„Ø­Ù…Ù„Ø§Øª">Campaigns</p>
                        <h3 class="mb-0 fw-bold" id="totalCampaigns">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1" data-en="Invoices" data-ar="Ø§Ù„ÙÙˆØ§ØªÙŠØ±">Invoices</p>
                        <h3 class="mb-0 fw-bold" id="totalInvoices">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#instances" data-en="Instances" data-ar="Ø§Ù„Ù…Ø«ÙŠÙ„Ø§Øª">Instances</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#payments" data-en="Payments" data-ar="Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª">Payments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#invoices" data-en="Invoices" data-ar="Ø§Ù„ÙÙˆØ§ØªÙŠØ±">Invoices</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#activity" data-en="Activity Log" data-ar="Ø³Ø¬Ù„ Ø§Ù„Ù†Ø´Ø§Ø·">Activity Log</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Instances Tab -->
            <div class="tab-pane fade show active" id="instances">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0" data-en="Customer Instances" data-ar="Ù…Ø«ÙŠÙ„Ø§Øª Ø§Ù„Ø¹Ù…ÙŠÙ„">Customer Instances</h5>
                        <button class="btn btn-sm btn-primary" onclick="addInstance()">
                            <i class="bi bi-plus-circle me-1"></i>
                            <span data-en="Add Instance" data-ar="Ø¥Ø¶Ø§ÙØ© Ù…Ø«ÙŠÙ„">Add Instance</span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th data-en="Instance Name" data-ar="Ø§Ø³Ù… Ø§Ù„Ù…Ø«ÙŠÙ„">Instance Name</th>
                                    <th data-en="Phone" data-ar="Ø§Ù„Ù‡Ø§ØªÙ">Phone</th>
                                    <th data-en="Status" data-ar="Ø§Ù„Ø­Ø§Ù„Ø©">Status</th>
                                    <th data-en="Messages" data-ar="Ø§Ù„Ø±Ø³Ø§Ø¦Ù„">Messages</th>
                                    <th data-en="Created Date" data-ar="ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¥Ù†Ø´Ø§Ø¡">Created Date</th>
                                    <th data-en="Actions" data-ar="Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="instancesTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payments Tab -->
            <div class="tab-pane fade" id="payments">
                <div class="dashboard-card">
                    <h5 class="mb-3" data-en="Payment History" data-ar="Ø³Ø¬Ù„ Ø§Ù„Ù…Ø¯ÙÙˆØ¹Ø§Øª">Payment History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th data-en="Date" data-ar="Ø§Ù„ØªØ§Ø±ÙŠØ®">Date</th>
                                    <th data-en="Amount" data-ar="Ø§Ù„Ù…Ø¨Ù„Øº">Amount</th>
                                    <th data-en="Method" data-ar="Ø·Ø±ÙŠÙ‚Ø© Ø§Ù„Ø¯ÙØ¹">Method</th>
                                    <th data-en="Status" data-ar="Ø§Ù„Ø­Ø§Ù„Ø©">Status</th>
                                    <th data-en="Invoice" data-ar="Ø§Ù„ÙØ§ØªÙˆØ±Ø©">Invoice</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Invoices Tab -->
            <div class="tab-pane fade" id="invoices">
                <div class="dashboard-card">
                    <h5 class="mb-3" data-en="Invoices" data-ar="Ø§Ù„ÙÙˆØ§ØªÙŠØ±">Invoices</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th data-en="Invoice #" data-ar="Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©">Invoice #</th>
                                    <th data-en="Date" data-ar="Ø§Ù„ØªØ§Ø±ÙŠØ®">Date</th>
                                    <th data-en="Amount" data-ar="Ø§Ù„Ù…Ø¨Ù„Øº">Amount</th>
                                    <th data-en="Status" data-ar="Ø§Ù„Ø­Ø§Ù„Ø©">Status</th>
                                    <th data-en="Actions" data-ar="Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Log Tab -->
            <div class="tab-pane fade" id="activity">
                <div class="dashboard-card">
                    <h5 class="mb-3" data-en="Activity Log" data-ar="Ø³Ø¬Ù„ Ø§Ù„Ù†Ø´Ø§Ø·">Activity Log</h5>
                    <div id="activityLogContainer">
                        <!-- Activity log will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Instance Modal -->
    <div class="modal fade" id="addInstanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" data-en="Add New Instance" data-ar="Ø¥Ø¶Ø§ÙØ© Ù…Ø«ÙŠÙ„ Ø¬Ø¯ÙŠØ¯">Add New Instance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addInstanceForm">
                        <div class="mb-3">
                            <label class="form-label" data-en="Instance Name" data-ar="Ø§Ø³Ù… Ø§Ù„Ù…Ø«ÙŠÙ„">Instance Name</label>
                            <input type="text" class="form-control" id="instanceName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-en="Phone Number" data-ar="Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ">Phone Number</label>
                            <input type="tel" class="form-control" id="instancePhone" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-ar="Ø¥Ù„ØºØ§Ø¡">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveInstance()" data-en="Add Instance" data-ar="Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ù…Ø«ÙŠÙ„">Add Instance</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/admin-customer-details.js"></script>
</body>
</html>


