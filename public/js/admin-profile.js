// Sample admin data
let adminData = {
    fullName: 'John Doe',
    email: 'john@admin.com',
    username: 'johndoe',
    role: 'super_admin',
    status: 'active',
    lastLogin: '2024-12-28 10:30:00'
};

function initAdminProfilePage() {
    checkAdminAuth();
    loadAdminData();
    loadSavedLanguage();
}

function loadAdminData() {
    // Get from sessionStorage if available
    const storedUsername = sessionStorage.getItem('adminUsername');
    if (storedUsername) {
        adminData.username = storedUsername;
    }
    
    // Update profile display
    document.getElementById('adminName').textContent = adminData.fullName;
    document.getElementById('adminEmail').textContent = adminData.email;
    document.getElementById('adminUsername').textContent = adminData.username;
    document.getElementById('adminRole').textContent = getRoleText(adminData.role);
    document.getElementById('adminStatus').textContent = 'Active';
    document.getElementById('lastLogin').textContent = getTimeAgo(adminData.lastLogin);
    
    // Update avatar
    const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(adminData.fullName)}&size=150&background=dc3545&color=fff`;
    document.getElementById('adminAvatar').src = avatarUrl;
    
    // Personal info section
    document.getElementById('fullName').textContent = adminData.fullName;
    document.getElementById('emailAddress').textContent = adminData.email;
    document.getElementById('usernameDisplay').textContent = adminData.username;
    document.getElementById('roleDisplay').textContent = getRoleText(adminData.role);
    
    // Fill edit form
    document.getElementById('editFullName').value = adminData.fullName;
    document.getElementById('editEmail').value = adminData.email;
    document.getElementById('editUsername').value = adminData.username;
}

function saveAdminProfile() {
    const fullName = document.getElementById('editFullName').value;
    const email = document.getElementById('editEmail').value;
    const username = document.getElementById('editUsername').value;
    
    if (!fullName || !email || !username) {
        alert('Please fill all fields');
        return;
    }
    
    // Update admin data
    adminData.fullName = fullName;
    adminData.email = email;
    adminData.username = username;
    
    // Update sessionStorage
    sessionStorage.setItem('adminUsername', username);
    
    // Reload display
    loadAdminData();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('editAdminModal'));
    modal.hide();
    
    showAlert('Profile updated successfully!', 'success');
}

function changeAdminPassword() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    
    if (!current || !newPass || !confirm) {
        showAlert('Please fill all password fields', 'danger');
        return;
    }
    
    if (newPass !== confirm) {
        showAlert('New passwords do not match', 'danger');
        return;
    }
    
    if (newPass.length < 6) {
        showAlert('Password must be at least 6 characters', 'danger');
        return;
    }
    
    // Here you would call API to change password
    showAlert('Password changed successfully!', 'success');
    document.getElementById('changePasswordForm').reset();
}

function getRoleText(role) {
    const roles = {
        'super_admin': 'Super Admin',
        'admin': 'Admin',
        'moderator': 'Moderator'
    };
    return roles[role] || role;
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (hours < 24) return `${hours} hours ago`;
    return `${days} days ago`;
}

function checkAdminAuth() {
    if (!sessionStorage.getItem('adminToken')) {
        window.location.href = 'admin-login.html';
    }
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
        sessionStorage.removeItem('adminUsername');
        sessionStorage.removeItem('isAdmin');
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
    setTimeout(() => alertDiv.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', initAdminProfilePage);