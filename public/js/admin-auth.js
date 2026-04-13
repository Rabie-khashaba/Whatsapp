// ========================================
// Admin Authentication
// ========================================

// Handle Admin Login Form
function handleAdminLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('adminUsername').value;
    const password = document.getElementById('adminPassword').value;
    
    // Validate inputs
    if (!username || !password) {
        showAlert('Please enter username and password', 'danger');
        return;
    }
    
    // Sample admin credentials (Replace with actual API call)
    const validAdmins = [
        { username: 'admin', password: 'admin123' },
        { username: 'superadmin', password: 'super123' }
    ];
    
    const admin = validAdmins.find(a => a.username === username && a.password === password);
    
    if (admin) {
        // Store admin token (different from customer token)
        sessionStorage.setItem('adminToken', 'admin-token-' + Date.now());
        sessionStorage.setItem('adminUsername', username);
        sessionStorage.setItem('isAdmin', 'true'); // Important flag
        
        showAlert('Login successful! Redirecting...', 'success');
        
        // Redirect to ADMIN dashboard
        setTimeout(() => {
            window.location.href = 'admin-dashboard.html';
        }, 1000);
    } else {
        showAlert('Invalid username or password', 'danger');
    }
}

// Check if user is admin
// Note: script.js already has a robust checkAdminAuth() function that handles all auth checks
// This function is kept for backward compatibility but defers to script.js if available
// Since script.js loads first and defines checkAdminAuth, this will only run if script.js is not loaded
function checkAdminAuth() {
    // If script.js has already run its auth check, don't do anything
    if (window.authCheckRan) {
        return true;
    }
    
    // Fallback for pages that don't use script.js
    const adminToken = sessionStorage.getItem('adminToken');
    const isAdmin = sessionStorage.getItem('isAdmin');
    
    // Prevent infinite loop
    if (sessionStorage.getItem('redirecting')) {
        return true;
    }
    
    // Get filename more reliably
    let fileName = window.location.pathname;
    if (fileName.includes('\\')) {
        fileName = fileName.split('\\').pop();
    } else {
        fileName = fileName.split('/').pop();
    }
    
    if (!fileName || fileName === '') {
        const href = window.location.href;
        fileName = href.substring(href.lastIndexOf('/') + 1).split('?')[0];
    }
    
    // If on admin login page but already authenticated, redirect once
    if (fileName === 'admin-login.html' && adminToken && isAdmin === 'true') {
        if (!window.redirectAttempted) {
            window.redirectAttempted = true;
            sessionStorage.setItem('redirecting', 'true');
            window.location.replace('admin-dashboard.html');
            return false;
        }
    }
    
    return true;
}

// Admin Logout
function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear admin session
        sessionStorage.removeItem('adminToken');
        sessionStorage.removeItem('adminUsername');
        sessionStorage.removeItem('isAdmin');
        
        // Redirect to admin login
        window.location.href = 'admin-login.html';
    }
}

// Show Alert
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

// Export functions
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        handleAdminLogin,
        checkAdminAuth,
        adminLogout
    };
}

// Note: Auth checking is now handled by script.js's DOMContentLoaded event
// This prevents multiple auth checks and infinite redirect loops