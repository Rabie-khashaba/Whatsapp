// ========================================
// Admin Customer Details
// ========================================

// Sample customer data (Replace with API call)
let customerData = null;
let customerId = null;

// Sample instances data
let instances = [];

// Sample payments data
let payments = [];

// Sample invoices data
let invoices = [];

// Sample activity log
let activityLog = [];

// ========================================
// Initialize Page
// ========================================
function initCustomerDetailsPage() {
    // Check authentication
    checkAdminAuth();
    
    // Get customer ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    customerId = urlParams.get('id');
    
    if (!customerId) {
        window.location.href = 'admin-customers.html';
        return;
    }
    
    // Load customer data
    loadCustomerData();
    
    // Apply saved language
    loadSavedLanguage();
}

// ========================================
// Load Customer Data
// ========================================
function loadCustomerData() {
    // Sample data - Replace with actual API call
    const sampleCustomers = [
        {
            id: 1,
            name: 'Ahmed Mohamed Ali',
            email: 'ahmed.ali@example.com',
            phone: '+20 100 123 4567',
            plan: 'pro',
            status: 'active',
            joinedDate: '2024-01-15',
            subscriptionDate: '2024-01-15',
            expiryDate: '2025-01-15',
            billingCycle: 'monthly',
            currentInstances: 3,
            maxInstances: 5,
            totalPaid: 299.00
        }
    ];
    
    customerData = sampleCustomers.find(c => c.id == customerId);
    
    if (!customerData) {
        alert('Customer not found!');
        window.location.href = 'admin-customers.html';
        return;
    }
    
    // Load related data
    loadInstances();
    loadPayments();
    loadInvoices();
    loadActivityLog();
    
    // Display customer data
    displayCustomerInfo();
    displayStats();
}

// ========================================
// Display Customer Info
// ========================================
function displayCustomerInfo() {
    document.getElementById('customerNameHeader').textContent = customerData.name;
    document.getElementById('customerAvatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(customerData.name)}&size=100&background=random`;
    document.getElementById('customerName').textContent = customerData.name;
    document.getElementById('customerEmail').textContent = customerData.email;
    document.getElementById('customerPhone').textContent = customerData.phone;
    document.getElementById('customerJoinDate').textContent = formatDate(customerData.joinedDate);
    document.getElementById('customerPlan').textContent = customerData.plan.toUpperCase();
    document.getElementById('customerBillingCycle').textContent = customerData.billingCycle.charAt(0).toUpperCase() + customerData.billingCycle.slice(1);
    document.getElementById('customerSubDate').textContent = formatDate(customerData.subscriptionDate);
    document.getElementById('customerExpiryDate').textContent = formatDate(customerData.expiryDate);
    document.getElementById('currentInstances').textContent = customerData.currentInstances;
    document.getElementById('maxInstances').textContent = customerData.maxInstances;
    document.getElementById('totalPaid').textContent = `$${customerData.totalPaid.toFixed(2)}`;
    
    // Status badge
    const statusBadge = document.getElementById('customerStatus');
    statusBadge.textContent = customerData.status.charAt(0).toUpperCase() + customerData.status.slice(1);
    statusBadge.className = `badge bg-${getStatusColor(customerData.status)} mb-3`;
}

// ========================================
// Display Stats
// ========================================
function displayStats() {
    document.getElementById('activeInstances').textContent = instances.filter(i => i.status === 'active').length;
    document.getElementById('totalMessages').textContent = instances.reduce((sum, i) => sum + i.messages, 0);
    document.getElementById('totalCampaigns').textContent = instances.reduce((sum, i) => sum + i.campaigns, 0);
    document.getElementById('totalInvoices').textContent = invoices.length;
}

// ========================================
// Load Instances
// ========================================
function loadInstances() {
    // Sample instances data
    instances = [
        {
            id: 1,
            name: 'instance1734',
            phone: '20111266019',
            status: 'active',
            messages: 5395,
            campaigns: 12,
            createdDate: '2024-01-15'
        },
        {
            id: 2,
            name: 'instance2048',
            phone: '20122334455',
            status: 'active',
            messages: 2130,
            campaigns: 5,
            createdDate: '2024-02-10'
        },
        {
            id: 3,
            name: 'instance3096',
            phone: '20155667788',
            status: 'inactive',
            messages: 850,
            campaigns: 2,
            createdDate: '2024-03-05'
        }
    ];
    
    displayInstances();
}

// ========================================
// Display Instances
// ========================================
function displayInstances() {
    const tbody = document.getElementById('instancesTableBody');
    tbody.innerHTML = '';
    
    if (instances.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No instances found
                </td>
            </tr>
        `;
        return;
    }
    
    instances.forEach(instance => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="fw-semibold">${instance.name}</td>
            <td>${instance.phone}</td>
            <td><span class="badge bg-${instance.status === 'active' ? 'success' : 'secondary'}">${instance.status}</span></td>
            <td>${instance.messages.toLocaleString()}</td>
            <td>${formatDate(instance.createdDate)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewInstance(${instance.id})" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteInstance(${instance.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// Load Payments
// ========================================
function loadPayments() {
    // Sample payments data
    payments = [
        {
            id: 1,
            date: '2024-12-15',
            amount: 29.99,
            method: 'Credit Card',
            status: 'completed',
            invoiceId: 'INV-001'
        },
        {
            id: 2,
            date: '2024-11-15',
            amount: 29.99,
            method: 'Vodafone Cash',
            status: 'completed',
            invoiceId: 'INV-002'
        },
        {
            id: 3,
            date: '2024-10-15',
            amount: 29.99,
            method: 'Bank Transfer',
            status: 'completed',
            invoiceId: 'INV-003'
        }
    ];
    
    displayPayments();
}

// ========================================
// Display Payments
// ========================================
function displayPayments() {
    const tbody = document.getElementById('paymentsTableBody');
    tbody.innerHTML = '';
    
    if (payments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No payments found
                </td>
            </tr>
        `;
        return;
    }
    
    payments.forEach(payment => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(payment.date)}</td>
            <td class="fw-semibold text-success">$${payment.amount.toFixed(2)}</td>
            <td>${payment.method}</td>
            <td><span class="badge bg-${payment.status === 'completed' ? 'success' : 'warning'}">${payment.status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice('${payment.invoiceId}')">
                    <i class="bi bi-receipt"></i> ${payment.invoiceId}
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// Load Invoices
// ========================================
function loadInvoices() {
    // Sample invoices data
    invoices = [
        {
            id: 'INV-001',
            date: '2024-12-15',
            amount: 29.99,
            status: 'paid'
        },
        {
            id: 'INV-002',
            date: '2024-11-15',
            amount: 29.99,
            status: 'paid'
        },
        {
            id: 'INV-003',
            date: '2024-10-15',
            amount: 29.99,
            status: 'paid'
        }
    ];
    
    displayInvoices();
}

// ========================================
// Display Invoices
// ========================================
function displayInvoices() {
    const tbody = document.getElementById('invoicesTableBody');
    tbody.innerHTML = '';
    
    if (invoices.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No invoices found
                </td>
            </tr>
        `;
        return;
    }
    
    invoices.forEach(invoice => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="fw-semibold">${invoice.id}</td>
            <td>${formatDate(invoice.date)}</td>
            <td class="fw-semibold">$${invoice.amount.toFixed(2)}</td>
            <td><span class="badge bg-${invoice.status === 'paid' ? 'success' : 'warning'}">${invoice.status}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewInvoice('${invoice.id}')">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary" onclick="downloadInvoice('${invoice.id}')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// Load Activity Log
// ========================================
function loadActivityLog() {
    // Sample activity log data
    activityLog = [
        {
            id: 1,
            type: 'login',
            description: 'Customer logged in',
            date: '2024-12-27 10:30:00',
            icon: 'bi-box-arrow-in-right',
            color: 'success'
        },
        {
            id: 2,
            type: 'payment',
            description: 'Payment completed - $29.99',
            date: '2024-12-15 14:20:00',
            icon: 'bi-credit-card',
            color: 'success'
        },
        {
            id: 3,
            type: 'instance',
            description: 'New instance created: instance3096',
            date: '2024-12-10 09:15:00',
            icon: 'bi-plus-circle',
            color: 'primary'
        },
        {
            id: 4,
            type: 'message',
            description: 'Sent 250 messages via instance1734',
            date: '2024-12-05 16:45:00',
            icon: 'bi-chat-dots',
            color: 'info'
        }
    ];
    
    displayActivityLog();
}

// ========================================
// Display Activity Log
// ========================================
function displayActivityLog() {
    const container = document.getElementById('activityLogContainer');
    container.innerHTML = '';
    
    if (activityLog.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p>No activity found</p>
            </div>
        `;
        return;
    }
    
    activityLog.forEach(activity => {
        const item = document.createElement('div');
        item.className = 'border-bottom pb-3 mb-3';
        item.innerHTML = `
            <div class="d-flex align-items-start gap-3">
                <div class="stats-icon" style="background: rgba(var(--bs-${activity.color}-rgb), 0.1); color: var(--bs-${activity.color}); width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="${activity.icon}"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-1 fw-semibold">${activity.description}</p>
                    <small class="text-muted">${activity.date}</small>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
}

// ========================================
// Actions
// ========================================
function editCustomerInfo() {
    // Redirect to edit page or open modal
    alert('Edit customer functionality - Coming soon!');
}

function deleteCustomerAccount() {
    if (confirm('Are you sure you want to delete this customer account? This action cannot be undone.')) {
        // API call to delete customer
        alert('Customer account deleted!');
        window.location.href = 'admin-customers.html';
    }
}

function addInstance() {
    if (customerData.currentInstances >= customerData.maxInstances) {
        alert('Customer has reached maximum instances limit!');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('addInstanceModal'));
    modal.show();
}

function saveInstance() {
    const name = document.getElementById('instanceName').value;
    const phone = document.getElementById('instancePhone').value;
    
    if (!name || !phone) {
        alert('Please fill in all fields');
        return;
    }
    
    const newInstance = {
        id: instances.length + 1,
        name,
        phone,
        status: 'active',
        messages: 0,
        campaigns: 0,
        createdDate: new Date().toISOString().split('T')[0]
    };
    
    instances.push(newInstance);
    customerData.currentInstances++;
    
    displayInstances();
    displayStats();
    displayCustomerInfo();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addInstanceModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('addInstanceForm').reset();
    
    showAlert('Instance added successfully!', 'success');
}

function viewInstance(id) {
    alert(`View instance ${id} - Coming soon!`);
}

function deleteInstance(id) {
    if (!confirm('Are you sure you want to delete this instance?')) return;
    
    instances = instances.filter(i => i.id !== id);
    customerData.currentInstances--;
    
    displayInstances();
    displayStats();
    displayCustomerInfo();
    
    showAlert('Instance deleted successfully!', 'success');
}

function viewInvoice(invoiceId) {
    alert(`View invoice ${invoiceId} - Coming soon!`);
}

function downloadInvoice(invoiceId) {
    alert(`Download invoice ${invoiceId} - Coming soon!`);
}

// ========================================
// Helper Functions
// ========================================
function getStatusColor(status) {
    const colors = {
        'active': 'success',
        'expired': 'danger',
        'pending': 'warning'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function checkAdminAuth() {
    const adminToken = sessionStorage.getItem('adminToken');
    if (!adminToken) {
        window.location.href = 'admin-login.html';
    }
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

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
// Initialize on Page Load
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initCustomerDetailsPage();
});