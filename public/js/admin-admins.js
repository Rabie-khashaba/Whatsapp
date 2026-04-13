let admins = [
    { id: 1, name: 'John Doe', email: 'john@admin.com', username: 'johndoe', role: 'super_admin', status: 'active', lastLogin: '2024-12-27 10:30:00' },
    { id: 2, name: 'Jane Smith', email: 'jane@admin.com', username: 'janesmith', role: 'admin', status: 'active', lastLogin: '2024-12-27 08:15:00' },
    { id: 3, name: 'Mike Johnson', email: 'mike@admin.com', username: 'mikej', role: 'moderator', status: 'active', lastLogin: '2024-12-26 16:45:00' }
];

function initAdminsPage() {
    checkAdminAuth();
    updateStats();
    displayAdmins();
    loadSavedLanguage();
}

function updateStats() {
    const activeCount = admins.filter(a => a.status === 'active').length;
    document.getElementById('totalAdmins').textContent = admins.length;
    document.getElementById('activeAdmins').textContent = activeCount;
}

function displayAdmins() {
    const tbody = document.getElementById('adminsTableBody');
    tbody.innerHTML = '';
    
    admins.forEach(admin => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(admin.name)}" width="32" height="32" class="rounded-circle">
                    <div>
                        <div class="fw-semibold">${admin.name}</div>
                        <small class="text-muted">@${admin.username}</small>
                    </div>
                </div>
            </td>
            <td>${admin.email}</td>
            <td><span class="badge bg-${getRoleBadge(admin.role)}">${admin.role.replace('_', ' ')}</span></td>
            <td><span class="badge bg-${admin.status === 'active' ? 'success' : 'secondary'}">${admin.status}</span></td>
            <td>${getTimeAgo(admin.lastLogin)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="editAdmin(${admin.id})"><i class="bi bi-pencil"></i></button>
                    ${admin.role !== 'super_admin' ? `<button class="btn btn-outline-danger" onclick="deleteAdmin(${admin.id})"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function saveAdmin() {
    const name = document.getElementById('adminName').value;
    const email = document.getElementById('adminEmail').value;
    const username = document.getElementById('adminUsername').value;
    const password = document.getElementById('adminPassword').value;
    const role = document.getElementById('adminRole').value;
    
    if (!name || !email || !username || !password || !role) {
        alert('Please fill all fields');
        return;
    }
    
    const newAdmin = {
        id: admins.length + 1,
        name,
        email,
        username,
        role,
        status: 'active',
        lastLogin: new Date().toISOString()
    };
    
    admins.push(newAdmin);
    updateStats();
    displayAdmins();
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('addAdminModal'));
    modal.hide();
    document.getElementById('addAdminForm').reset();
    
    alert('Admin added successfully!');
}

function editAdmin(id) {
    alert('Edit admin ' + id + ' - Coming soon!');
}

function deleteAdmin(id) {
    if (!confirm('Are you sure you want to delete this admin?')) return;
    admins = admins.filter(a => a.id !== id);
    updateStats();
    displayAdmins();
    alert('Admin deleted!');
}

function getRoleBadge(role) {
    const badges = { super_admin: 'danger', admin: 'primary', moderator: 'warning' };
    return badges[role] || 'secondary';
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
    if (!sessionStorage.getItem('adminToken')) window.location.href = 'admin-login.html';
}

function adminLogout() {
    if (confirm('Logout?')) {
        sessionStorage.removeItem('adminToken');
        window.location.href = 'admin-login.html';
    }
}

document.addEventListener('DOMContentLoaded', initAdminsPage);