// ========================================
// Admin Payments Queue Management
// ========================================

// Sample Pending Payments Data
let pendingPayments = [
    {
        id: 1,
        customerId: 1,
        customerName: 'Ahmed Mohamed Ali',
        customerEmail: 'ahmed.ali@example.com',
        customerPhone: '+20 100 123 4567',
        plan: 'Pro',
        billingCycle: 'monthly',
        amount: 29.99,
        paymentMethod: 'vodafone_cash',
        transactionId: 'VF123456789',
        submittedDate: '2024-12-27 10:30:00',
        proofImage: 'https://via.placeholder.com/400x300?text=Payment+Proof',
        notes: 'Payment made via Vodafone Cash. Transaction ID: VF123456789',
        status: 'pending'
    },
    {
        id: 2,
        customerId: 2,
        customerName: 'Sara Ahmed Mahmoud',
        customerEmail: 'sara.ahmed@example.com',
        customerPhone: '+20 111 555 5555',
        plan: 'Enterprise',
        billingCycle: 'yearly',
        amount: 999.99,
        paymentMethod: 'bank_transfer',
        transactionId: 'BT987654321',
        submittedDate: '2024-12-27 09:15:00',
        proofImage: 'https://via.placeholder.com/400x300?text=Bank+Transfer+Receipt',
        notes: 'Bank transfer completed. Reference: BT987654321',
        status: 'pending'
    },
    {
        id: 3,
        customerId: 3,
        customerName: 'Omar Abdullah Karim',
        customerEmail: 'omar.abdullah@example.com',
        customerPhone: '+20 102 777 7777',
        plan: 'Basic',
        billingCycle: 'monthly',
        amount: 9.99,
        paymentMethod: 'vodafone_cash',
        transactionId: 'VF555444333',
        submittedDate: '2024-12-26 16:45:00',
        proofImage: 'https://via.placeholder.com/400x300?text=Vodafone+Cash+Screenshot',
        notes: 'Vodafone Cash payment screenshot attached',
        status: 'pending'
    }
];

let filteredPayments = [...pendingPayments];
let selectedPaymentId = null;

// ========================================
// Initialize Page
// ========================================
function initPaymentsQueuePage() {
    // Check authentication
    checkAdminAuth();
    
    // Update stats
    updateStats();
    
    // Display payments
    displayPayments();
    
    // Apply saved language
    loadSavedLanguage();
}

// ========================================
// Update Statistics
// ========================================
function updateStats() {
    const totalPending = pendingPayments.length;
    const totalAmount = pendingPayments.reduce((sum, p) => sum + p.amount, 0);
    const vodafoneCount = pendingPayments.filter(p => p.paymentMethod === 'vodafone_cash').length;
    const bankCount = pendingPayments.filter(p => p.paymentMethod === 'bank_transfer').length;
    
    document.getElementById('totalPending').textContent = totalPending;
    document.getElementById('pendingCount').textContent = totalPending;
    document.getElementById('totalAmount').textContent = `$${totalAmount.toFixed(2)}`;
    document.getElementById('vodafoneCash').textContent = vodafoneCount;
    document.getElementById('bankTransfer').textContent = bankCount;
}

// ========================================
// Display Payments
// ========================================
function displayPayments() {
    const container = document.getElementById('paymentsContainer');
    container.innerHTML = '';
    
    if (filteredPayments.length === 0) {
        container.innerHTML = `
            <div class="dashboard-card text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 64px;"></i>
                <h5 class="mt-3 text-muted" data-en="No Pending Payments" data-ar="لا توجد مدفوعات معلقة">No Pending Payments</h5>
                <p class="text-muted" data-en="All payments have been processed" data-ar="تم معالجة جميع المدفوعات">All payments have been processed</p>
            </div>
        `;
        return;
    }
    
    filteredPayments.forEach(payment => {
        const paymentCard = createPaymentCard(payment);
        container.appendChild(paymentCard);
    });
}

// ========================================
// Create Payment Card
// ========================================
function createPaymentCard(payment) {
    const card = document.createElement('div');
    card.className = 'dashboard-card mb-3';
    
    const methodIcon = getPaymentMethodIcon(payment.paymentMethod);
    const methodText = getPaymentMethodText(payment.paymentMethod);
    const timeAgo = getTimeAgo(payment.submittedDate);
    
    card.innerHTML = `
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24; width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="${methodIcon} fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${payment.customerName}</h6>
                        <small class="text-muted d-block">${payment.customerEmail}</small>
                        <small class="text-muted d-block">${payment.customerPhone}</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 mt-3 mt-lg-0">
                <label class="text-muted small d-block" data-en="Plan" data-ar="الباقة">Plan</label>
                <span class="badge bg-primary">${payment.plan}</span>
                <small class="d-block text-muted mt-1">${payment.billingCycle}</small>
            </div>
            <div class="col-lg-2 mt-3 mt-lg-0">
                <label class="text-muted small d-block" data-en="Amount" data-ar="المبلغ">Amount</label>
                <h5 class="mb-0 text-success fw-bold">$${payment.amount.toFixed(2)}</h5>
                <small class="text-muted d-block">${methodText}</small>
            </div>
            <div class="col-lg-2 mt-3 mt-lg-0 text-end">
                <small class="text-muted d-block mb-2">${timeAgo}</small>
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="viewPaymentDetails(${payment.id})" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="approvePayment(${payment.id})" title="Approve">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="rejectPayment(${payment.id})" title="Reject">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

// ========================================
// View Payment Details
// ========================================
function viewPaymentDetails(id) {
    const payment = pendingPayments.find(p => p.id === id);
    if (!payment) return;
    
    selectedPaymentId = id;
    
    // Fill modal with payment details
    document.getElementById('modalCustomerName').textContent = payment.customerName;
    document.getElementById('modalCustomerEmail').textContent = payment.customerEmail;
    document.getElementById('modalCustomerPhone').textContent = payment.customerPhone;
    document.getElementById('modalPlan').textContent = payment.plan;
    document.getElementById('modalBillingCycle').textContent = payment.billingCycle.charAt(0).toUpperCase() + payment.billingCycle.slice(1);
    document.getElementById('modalAmount').textContent = `$${payment.amount.toFixed(2)}`;
    document.getElementById('modalPaymentMethod').textContent = getPaymentMethodText(payment.paymentMethod);
    document.getElementById('modalTransactionId').textContent = payment.transactionId;
    document.getElementById('modalSubmittedDate').textContent = formatDateTime(payment.submittedDate);
    document.getElementById('modalNotes').textContent = payment.notes || 'No notes provided';
    
    // Display proof image
    const proofContainer = document.getElementById('modalProofContainer');
    proofContainer.innerHTML = `
        <img src="${payment.proofImage}" alt="Payment Proof" class="img-fluid rounded" style="max-height: 400px;">
        <div class="mt-2">
            <a href="${payment.proofImage}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-download me-1"></i>
                <span data-en="Download Proof" data-ar="تحميل الإثبات">Download Proof</span>
            </a>
        </div>
    `;
    
    // Reset rejection reason
    document.getElementById('rejectionReasonContainer').style.display = 'none';
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectBtn').style.display = 'inline-block';
    document.getElementById('approveBtn').style.display = 'inline-block';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
    modal.show();
}

// ========================================
// Show Rejection Reason Field
// ========================================
function showRejectionReason() {
    const container = document.getElementById('rejectionReasonContainer');
    container.style.display = 'block';
    document.getElementById('rejectBtn').textContent = 'Confirm Rejection';
    document.getElementById('rejectBtn').onclick = function() {
        confirmRejection();
    };
}

// ========================================
// Approve Payment
// ========================================
function approvePayment(id) {
    if (!confirm('Are you sure you want to approve this payment?')) return;
    
    const paymentIndex = pendingPayments.findIndex(p => p.id === id);
    if (paymentIndex === -1) return;
    
    const payment = pendingPayments[paymentIndex];
    
    // Remove from pending
    pendingPayments.splice(paymentIndex, 1);
    filteredPayments = [...pendingPayments];
    
    // Here you would:
    // 1. Update payment status in backend
    // 2. Activate customer subscription
    // 3. Generate invoice
    // 4. Send confirmation email to customer
    
    updateStats();
    displayPayments();
    
    showAlert(`Payment of $${payment.amount.toFixed(2)} from ${payment.customerName} has been approved!`, 'success');
}

// ========================================
// Approve Payment from Modal
// ========================================
function approvePaymentFromModal() {
    if (!selectedPaymentId) return;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentDetailsModal'));
    modal.hide();
    
    // Approve payment
    approvePayment(selectedPaymentId);
    selectedPaymentId = null;
}

// ========================================
// Reject Payment
// ========================================
function rejectPayment(id) {
    if (!confirm('Are you sure you want to reject this payment?')) return;
    
    const reason = prompt('Please enter rejection reason:');
    if (!reason) return;
    
    const paymentIndex = pendingPayments.findIndex(p => p.id === id);
    if (paymentIndex === -1) return;
    
    const payment = pendingPayments[paymentIndex];
    
    // Remove from pending
    pendingPayments.splice(paymentIndex, 1);
    filteredPayments = [...pendingPayments];
    
    // Here you would:
    // 1. Update payment status in backend
    // 2. Send rejection email to customer with reason
    // 3. Log the rejection
    
    updateStats();
    displayPayments();
    
    showAlert(`Payment from ${payment.customerName} has been rejected.`, 'warning');
}

// ========================================
// Confirm Rejection from Modal
// ========================================
function confirmRejection() {
    const reason = document.getElementById('rejectionReason').value.trim();
    
    if (!reason) {
        alert('Please enter a rejection reason');
        return;
    }
    
    if (!selectedPaymentId) return;
    
    const paymentIndex = pendingPayments.findIndex(p => p.id === selectedPaymentId);
    if (paymentIndex === -1) return;
    
    const payment = pendingPayments[paymentIndex];
    
    // Remove from pending
    pendingPayments.splice(paymentIndex, 1);
    filteredPayments = [...pendingPayments];
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentDetailsModal'));
    modal.hide();
    
    updateStats();
    displayPayments();
    
    showAlert(`Payment from ${payment.customerName} has been rejected. Reason: ${reason}`, 'warning');
    selectedPaymentId = null;
}

// ========================================
// Apply Filters
// ========================================
function applyFilters() {
    const method = document.getElementById('methodFilter').value;
    const sort = document.getElementById('sortFilter').value;
    
    // Filter by payment method
    filteredPayments = pendingPayments.filter(payment => {
        return !method || payment.paymentMethod === method;
    });
    
    // Sort payments
    filteredPayments.sort((a, b) => {
        switch (sort) {
            case 'date_asc':
                return new Date(a.submittedDate) - new Date(b.submittedDate);
            case 'date_desc':
                return new Date(b.submittedDate) - new Date(a.submittedDate);
            case 'amount_asc':
                return a.amount - b.amount;
            case 'amount_desc':
                return b.amount - a.amount;
            default:
                return 0;
        }
    });
    
    displayPayments();
}

// ========================================
// Reset Filters
// ========================================
function resetFilters() {
    document.getElementById('methodFilter').value = '';
    document.getElementById('sortFilter').value = 'date_desc';
    filteredPayments = [...pendingPayments];
    displayPayments();
}

// ========================================
// Refresh Payments
// ========================================
function refreshPayments() {
    // Here you would call API to fetch latest payments
    showAlert('Payments refreshed!', 'info');
    
    // Simulate refresh with animation
    const container = document.getElementById('paymentsContainer');
    container.style.opacity = '0.5';
    
    setTimeout(() => {
        container.style.opacity = '1';
    }, 500);
}

// ========================================
// Helper Functions
// ========================================
function getPaymentMethodIcon(method) {
    const icons = {
        'vodafone_cash': 'bi bi-phone',
        'bank_transfer': 'bi bi-bank',
        'credit_card': 'bi bi-credit-card'
    };
    return icons[method] || 'bi bi-wallet2';
}

function getPaymentMethodText(method) {
    const texts = {
        'vodafone_cash': 'Vodafone Cash',
        'bank_transfer': 'Bank Transfer',
        'credit_card': 'Credit Card'
    };
    return texts[method] || method;
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 60) return `${minutes} minutes ago`;
    if (hours < 24) return `${hours} hours ago`;
    return `${days} days ago`;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
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
    }, 4000);
}

// ========================================
// Initialize on Page Load
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initPaymentsQueuePage();
});