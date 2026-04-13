function initReportsPage() {
    checkAdminAuth();
    loadSavedLanguage();
    setDefaultDates();
}

function setDefaultDates() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
}

function updateReports() {
    const period = document.getElementById('timePeriod').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    // Here you would call API to fetch reports data
    alert('Reports updated for period: ' + period);
}

function checkAdminAuth() {
    if (!sessionStorage.getItem('adminToken')) {
        window.location.href = 'admin-login.html';
    }
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

document.addEventListener('DOMContentLoaded', initReportsPage);