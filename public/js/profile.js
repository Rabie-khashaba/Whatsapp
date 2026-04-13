// Sample user data (Replace with API call)
let userData = {
    fullName: 'Ahmed Mohamed Ali',
    email: 'ahmed.ali@example.com',
    phone: '+20 100 123 4567',
    country: 'Egypt',
    plan: 'Pro',
    billingCycle: 'monthly',
    memberSince: '2024-01-15',
    expiryDate: '2025-01-15',
    status: 'active',
    currentInstances: 3,
    maxInstances: 5,
    totalPaid: 299.99
};

function initProfilePage() {
    checkAuth();
    loadUserData();
    loadSavedLanguage();
}

function loadUserData() {
    // Update profile info
    document.getElementById('userName').textContent = userData.fullName;
    document.getElementById('userEmail').textContent = userData.email;
    document.getElementById('userPhone').textContent = userData.phone;
    document.getElementById('userPlan').textContent = `${userData.plan.toUpperCase()} PLAN`;
    
    // Update avatars
    const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.fullName)}&size=150&background=25D366&color=fff`;
    document.getElementById('userAvatar').src = avatarUrl;
    document.getElementById('userAvatarTop').src = avatarUrl.replace('size=150', 'size=35');
    
    // Account status
    document.getElementById('memberSince').textContent = formatDate(userData.memberSince);
    document.getElementById('subStatus').textContent = userData.status.charAt(0).toUpperCase() + userData.status.slice(1);
    document.getElementById('expiryDate').textContent = formatDate(userData.expiryDate);
    
    // Personal info
    document.getElementById('fullName').textContent = userData.fullName;
    document.getElementById('emailAddress').textContent = userData.email;
    document.getElementById('phoneNumber').textContent = userData.phone;
    document.getElementById('country').textContent = userData.country;
    
    // Subscription details
    document.getElementById('currentPlan').textContent = userData.plan;
    document.getElementById('billingCycle').textContent = userData.billingCycle.charAt(0).toUpperCase() + userData.billingCycle.slice(1);
    document.getElementById('currentInstances').textContent = userData.currentInstances;
    document.getElementById('maxInstances').textContent = userData.maxInstances;
    document.getElementById('totalPaid').textContent = `$${userData.totalPaid.toFixed(2)}`;
    
    // Fill edit form
    document.getElementById('editFullName').value = userData.fullName;
    document.getElementById('editEmail').value = userData.email;
    document.getElementById('editPhone').value = userData.phone;
    document.getElementById('editCountry').value = userData.country;
}

function saveProfile() {
    const fullName = document.getElementById('editFullName').value;
    const email = document.getElementById('editEmail').value;
    const phone = document.getElementById('editPhone').value;
    const country = document.getElementById('editCountry').value;
    
    if (!fullName || !email || !phone) {
        alert('Please fill all fields');
        return;
    }
    
    // Update user data
    userData.fullName = fullName;
    userData.email = email;
    userData.phone = phone;
    userData.country = country;
    
    // Reload display
    loadUserData();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
    modal.hide();
    
    showAlert('Profile updated successfully!', 'success');
}

function changePassword() {
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

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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

document.addEventListener('DOMContentLoaded', initProfilePage);