// ========================================
// Admin Subscriptions Management
// ========================================

let subscriptions = [
    { id: 1, customerId: 1, customerName: 'Ahmed Mohamed', customerEmail: 'ahmed@example.com', plan: 'pro', billingCycle: 'monthly', startDate: '2024-01-15', expiryDate: '2025-01-15', status: 'active', amount: 29.99 },
    { id: 2, customerId: 2, customerName: 'Sara Ahmed', customerEmail: 'sara@example.com', plan: 'enterprise', billingCycle: 'yearly', startDate: '2024-02-01', expiryDate: '2025-02-01', status: 'active', amount: 999.99 },
    { id: 3, customerId: 3, customerName: 'Omar Ali', customerEmail: 'omar@example.com', plan: 'basic', billingCycle: 'monthly', startDate: '2024-12-20', expiryDate: '2025-01-02', status: 'expiring', amount: 9.99 },
    { id: 4, customerId: 4, customerName: 'Mohamed Hassan', customerEmail: 'mohamed@example.com', plan: 'pro', billingCycle: 'monthly', startDate: '2024-11-15', expiryDate: '2024-12-30', status: 'expiring', amount: 29.99 },
    { id: 5, customerId: 5, customerName: 'Fatma Hussein', customerEmail: 'fatma@example.com', plan: 'basic', billingCycle: 'yearly', startDate: '2024-03-10', expiryDate: '2025-03-10', status: 'active', amount: 99.99 }
];

let filteredSubscriptions = [...subscriptions];
let currentPage = 1;
const itemsPerPage = 10;

function initSubscriptionsPage() {
    checkAdminAuth();
    
    // Check if filtering by status from URL
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    if (statusParam) {
        document.getElementById('statusFilter').value = statusParam;
        applyFilters();
    }
    
    updateStats();
    displaySubscriptions();
    loadSavedLanguage();
}

function updateStats() {
    const active = subscriptions.filter(s => s.status === 'active');
    const expiring = subscriptions.filter(s => s.status === 'expiring');
    const monthly = subscriptions.filter(s => s.billingCycle === 'monthly');
    const mrr = monthly.reduce((sum, s) => sum + s.amount, 0);
    
    document.getElementById('totalActive').textContent = active.length;
    document.getElementById('totalExpiring').textContent = expiring.length;
    document.getElementById('monthlyRenewals').textContent = monthly.length;
    document.getElementById('totalMRR').textContent = `$${mrr.toFixed(2)}`;
}

function displaySubscriptions() {
    const tbody = document.getElementById('subscriptionsTableBody');
    tbody.innerHTML = '';
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredSubscriptions.length);
    const pageSubs = filteredSubscriptions.slice(startIndex, endIndex);
    
    if (pageSubs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No subscriptions found</td></tr>';
        return;
    }
    
    pageSubs.forEach((sub, index) => {
        const daysLeft = calculateDaysLeft(sub.expiryDate);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>
                <div class="fw-semibold">${sub.customerName}</div>
                <small class="text-muted">${sub.customerEmail}</small>
            </td>
            <td><span class="badge bg-${getPlanColor(sub.plan)}">${sub.plan.toUpperCase()}</span></td>
            <td>${sub.billingCycle.charAt(0).toUpperCase() + sub.billingCycle.slice(1)}</td>
            <td>${formatDate(sub.startDate)}</td>
            <td>${formatDate(sub.expiryDate)}</td>
            <td>
                <span class="badge bg-${getDaysLeftColor(daysLeft)}">${daysLeft} days</span>
            </td>
            <td><span class="badge bg-${sub.status === 'active' ? 'success' : 'warning'}">${sub.status}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewSubscription(${sub.id})" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary" onclick="renewSubscription(${sub.id})" title="Renew">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="cancelSubscription(${sub.id})" title="Cancel">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('showingFrom').textContent = startIndex + 1;
    document.getElementById('showingTo').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredSubscriptions.length;
    generatePagination();
}

function calculateDaysLeft(expiryDate) {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

function getDaysLeftColor(days) {
    if (days <= 3) return 'danger';
    if (days <= 7) return 'warning';
    return 'success';
}

function getPlanColor(plan) {
    const colors = { basic: 'secondary', pro: 'primary', enterprise: 'warning' };
    return colors[plan] || 'secondary';
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const plan = document.getElementById('planFilter').value;
    const cycle = document.getElementById('cycleFilter').value;
    
    filteredSubscriptions = subscriptions.filter(s => {
        const matchSearch = !search || s.customerName.toLowerCase().includes(search) || s.customerEmail.toLowerCase().includes(search);
        const matchStatus = !status || s.status === status;
        const matchPlan = !plan || s.plan === plan;
        const matchCycle = !cycle || s.billingCycle === cycle;
        return matchSearch && matchStatus && matchPlan && matchCycle;
    });
    
    currentPage = 1;
    displaySubscriptions();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('planFilter').value = '';
    document.getElementById('cycleFilter').value = '';
    filteredSubscriptions = [...subscriptions];
    currentPage = 1;
    displaySubscriptions();
}

function generatePagination() {
    const totalPages = Math.ceil(filteredSubscriptions.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>`;
    pagination.appendChild(prevLi);
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>`;
            pagination.appendChild(li);
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = '<span class="page-link">...</span>';
            pagination.appendChild(li);
        }
    }
    
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>`;
    pagination.appendChild(nextLi);
}

function changePage(page) {
    const totalPages = Math.ceil(filteredSubscriptions.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displaySubscriptions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function viewSubscription(id) {
    const sub = subscriptions.find(s => s.id === id);
    if (sub) {
        window.location.href = `admin-customer-details.html?id=${sub.customerId}`;
    }
}

function renewSubscription(id) {
    if (!confirm('Renew this subscription?')) return;
    
    const subIndex = subscriptions.findIndex(s => s.id === id);
    if (subIndex !== -1) {
        const sub = subscriptions[subIndex];
        const currentExpiry = new Date(sub.expiryDate);
        
        if (sub.billingCycle === 'monthly') {
            currentExpiry.setMonth(currentExpiry.getMonth() + 1);
        } else {
            currentExpiry.setFullYear(currentExpiry.getFullYear() + 1);
        }
        
        subscriptions[subIndex].expiryDate = currentExpiry.toISOString().split('T')[0];
        subscriptions[subIndex].status = 'active';
        
        filteredSubscriptions = [...subscriptions];
        displaySubscriptions();
        updateStats();
        
        alert('Subscription renewed successfully!');
    }
}

function cancelSubscription(id) {
    if (!confirm('Are you sure you want to cancel this subscription?')) return;
    
    subscriptions = subscriptions.filter(s => s.id !== id);
    filteredSubscriptions = filteredSubscriptions.filter(s => s.id !== id);
    
    displaySubscriptions();
    updateStats();
    
    alert('Subscription cancelled!');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function checkAdminAuth() {
    if (!sessionStorage.getItem('adminToken')) window.location.href = 'admin-login.html';
}

function adminLogout() {
    if (confirm('Logout?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

document.addEventListener('DOMContentLoaded', initSubscriptionsPage);