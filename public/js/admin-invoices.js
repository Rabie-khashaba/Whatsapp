let invoices = [
    { id: 'INV-001', customerId: 1, customerName: 'Ahmed Mohamed', plan: 'Pro', amount: 29.99, date: '2024-12-15', status: 'paid' },
    { id: 'INV-002', customerId: 2, customerName: 'Sara Ahmed', plan: 'Enterprise', amount: 999.99, date: '2024-12-10', status: 'paid' },
    { id: 'INV-003', customerId: 3, customerName: 'Omar Ali', plan: 'Basic', amount: 9.99, date: '2024-12-05', status: 'paid' },
    { id: 'INV-004', customerId: 4, customerName: 'Mohamed Ibrahim', plan: 'Basic', amount: 9.99, date: '2024-11-20', status: 'paid' }
];

let filteredInvoices = [...invoices];

function initInvoicesPage() {
    checkAdminAuth();
    updateStats();
    displayInvoices();
    loadSavedLanguage();
}

function updateStats() {
    const totalAmount = invoices.reduce((sum, i) => sum + i.amount, 0);
    const paidCount = invoices.filter(i => i.status === 'paid').length;
    
    document.getElementById('totalInvoices').textContent = invoices.length;
    document.getElementById('paidInvoices').textContent = paidCount;
    document.getElementById('totalAmount').textContent = `$${totalAmount.toFixed(2)}`;
}

function displayInvoices() {
    const tbody = document.getElementById('invoicesTableBody');
    tbody.innerHTML = '';
    
    if (filteredInvoices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No invoices found</td></tr>';
        return;
    }
    
    filteredInvoices.forEach(invoice => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="fw-semibold">${invoice.id}</td>
            <td>${invoice.customerName}</td>
            <td><span class="badge bg-primary">${invoice.plan}</span></td>
            <td class="fw-semibold text-success">$${invoice.amount.toFixed(2)}</td>
            <td>${formatDate(invoice.date)}</td>
            <td><span class="badge bg-${invoice.status === 'paid' ? 'success' : 'warning'}">${invoice.status}</span></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewInvoice('${invoice.id}')"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-outline-secondary" onclick="downloadInvoice('${invoice.id}')"><i class="bi bi-download"></i></button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    
    filteredInvoices = invoices.filter(i => {
        const matchSearch = !search || i.id.toLowerCase().includes(search) || i.customerName.toLowerCase().includes(search);
        const matchStatus = !status || i.status === status;
        return matchSearch && matchStatus;
    });
    
    displayInvoices();
}

function viewInvoice(id) {
    window.open(`invoice-view.html?id=${id}`, '_blank');
}

function downloadInvoice(id) {
    window.open(`invoice-view.html?id=${id}`, '_blank');
    setTimeout(() => {
        alert('Click "Download PDF" button in the new window to download the invoice.');
    }, 500);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function checkAdminAuth() {
    if (!sessionStorage.getItem('adminToken')) window.location.href = 'admin-login.html';
}

function adminLogout() {
    if (confirm('Are you sure?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

document.addEventListener('DOMContentLoaded', initInvoicesPage);