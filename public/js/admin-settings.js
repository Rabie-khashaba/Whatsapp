function initSettingsPage() {
    checkAdminAuth();
    loadSavedLanguage();
}

function saveSettings(type) {
    // Here you would call API to save settings
    let message = '';
    
    switch(type) {
        case 'general':
            message = 'General settings saved successfully!';
            break;
        case 'payment':
            message = 'Payment settings saved successfully!';
            break;
        case 'email':
            message = 'Email settings saved successfully!';
            break;
        case 'notifications':
            message = 'Notification settings saved successfully!';
            break;
        default:
            message = 'Settings saved successfully!';
    }
    
    showAlert(message, 'success');
}

function testEmail() {
    // Here you would call API to send test email
    showAlert('Test email sent! Please check your inbox.', 'info');
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

document.addEventListener('DOMContentLoaded', initSettingsPage);