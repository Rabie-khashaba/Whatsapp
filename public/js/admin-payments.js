// ========================================
// Admin All Payments Management
// ========================================

let payments = [
    { id: 1, customerId: 1, customerName: 'Ahmed Mohamed', customerEmail: 'ahmed@example.com', plan: 'Pro', amount: 29.99, method: 'vodafone_cash', date: '2024-12-15', status: 'completed', invoiceId: 'INV-001' },
    { id: 2, customerId: 2, customerName: 'Sara Ahmed', customerEmail: 'sara@example.com', plan: 'Enterprise', amount: 999.99, method: 'bank_transfer', date: '2024-12-10', status: 'completed', invoiceId: 'INV-002' },
    { id: 3, customerId: 3, customerName: 'Omar Ali', customerEmail: 'omar@example.com', plan: 'Basic', amount: 9.99, method: 'credit_card', date: '2024-12-05', status: 'completed', invoiceId: 'INV-003' },
    { id: 4, customerId: 4, customerName: 'Fatma Hassan', customerEmail: 'fatma@example.com', plan: 'Pro', amount: 29.99, method: 'vodafone_cash', date: '2024-11-28', status: 'rejected', invoiceId: null },
    { id: 5, customerId: 5, customerName: 'Mohamed Ibrahim', customerEmail: 'mohamed@example.com', plan: 'Basic', amount: 9.99, method: 'bank_transfer', date: '2024-11-20', status: 'completed', invoiceId: 'INV-004' }
];

let filteredPayments = [...payments];
let currentPage = 1;
const itemsPerPage = 10;

function initPaymentsPage() {
    checkAdminAuth();
    updateStats();
    displayPayments();
    loadSavedLanguage();
}

function updateStats() {
    const completed = payments.filter(p => p.status === 'completed');
    const totalRevenue = completed.reduce((sum, p) => sum + p.amount, 0);
    const thisMonth = completed.filter(p => new Date(p.date).getMonth() === new Date().getMonth());
    const monthlyRevenue = thisMonth.reduce((sum, p) => sum + p.amount, 0);
    const rejected = payments.filter(p => p.status === 'rejected').length;
    
    document.getElementById('totalCompleted').textContent = completed.length;
    document.getElementById('totalRevenue').textContent = `$${totalRevenue.toFixed(2)}`;
    document.getElementById('monthlyRevenue').textContent = `$${monthlyRevenue.toFixed(2)}`;
    document.getElementById('totalRejected').textContent = rejected;
}

function displayPayments() {
    const tbody = document.getElementById('paymentsTableBody');
    tbody.innerHTML = '';
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredPayments.length);
    const pagePayments = filteredPayments.slice(startIndex, endIndex);
    
    if (pagePayments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No payments found</td></tr>';
        return;
    }
    
    pagePayments.forEach((payment, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>
                <div class="fw-semibold">${payment.customerName}</div>
                <small class="text-muted">${payment.customerEmail}</small>
            </td>
            <td><span class="badge bg-primary">${payment.plan}</span></td>
            <td class="fw-semibold text-success">$${payment.amount.toFixed(2)}</td>
            <td>${getMethodText(payment.method)}</td>
            <td>${formatDate(payment.date)}</td>
            <td><span class="badge bg-${payment.status === 'completed' ? 'success' : 'danger'}">${payment.status}</span></td>
            <td>${payment.invoiceId ? `<a href="#" onclick="viewInvoice('${payment.invoiceId}')">${payment.invoiceId}</a>` : '-'}</td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('showingFrom').textContent = startIndex + 1;
    document.getElementById('showingTo').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredPayments.length;
    generatePagination();
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const method = document.getElementById('methodFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    filteredPayments = payments.filter(p => {
        const matchSearch = !search || p.customerName.toLowerCase().includes(search) || p.customerEmail.toLowerCase().includes(search);
        const matchStatus = !status || p.status === status;
        const matchMethod = !method || p.method === method;
        const matchDateFrom = !dateFrom || p.date >= dateFrom;
        const matchDateTo = !dateTo || p.date <= dateTo;
        return matchSearch && matchStatus && matchMethod && matchDateFrom && matchDateTo;
    });
    
    currentPage = 1;
    displayPayments();
}

function generatePagination() {
    const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
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
    const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayPayments();
    }
}

function viewInvoice(invoiceId) {
    window.location.href = `admin-invoices.html?id=${invoiceId}`;
}

function getMethodText(method) {
    const methods = { vodafone_cash: 'Vodafone Cash', bank_transfer: 'Bank Transfer', credit_card: 'Credit Card' };
    return methods[method] || method;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function checkAdminAuth() {
    if (!sessionStorage.getItem('adminToken')) window.location.href = 'admin-login.html';
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

document.addEventListener('DOMContentLoaded', initPaymentsPage);