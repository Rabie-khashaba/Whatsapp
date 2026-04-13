// ========================================
// Dashboard Specific Functions
// ========================================

// Toggle between Grid and Table View
function toggleView(viewType) {
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');
    const buttons = document.querySelectorAll('.btn-group button');
    
    if (viewType === 'grid') {
        gridView.classList.remove('d-none');
        tableView.classList.add('d-none');
        buttons[0].classList.add('active', 'btn-primary');
        buttons[0].classList.remove('btn-outline-primary');
        buttons[1].classList.remove('active', 'btn-primary');
        buttons[1].classList.add('btn-outline-primary');
    } else {
        gridView.classList.add('d-none');
        tableView.classList.remove('d-none');
        buttons[1].classList.add('active', 'btn-primary');
        buttons[1].classList.remove('btn-outline-primary');
        buttons[0].classList.remove('active', 'btn-primary');
        buttons[0].classList.add('btn-outline-primary');
    }
}

// View Instance Details
function viewInstance(instanceId) {
    // Store instance ID and redirect to instance details page
    sessionStorage.setItem('currentInstance', instanceId);
    window.location.href = 'instance-details.html';
}

// Edit Instance
function editInstance(instanceId) {
    // Here you would open an edit modal or redirect to edit page
    showAlert(`Editing instance: ${instanceId}`, 'info');
}

// Delete Instance
function deleteInstance(instanceId) {
    if (confirm('Are you sure you want to delete this instance?')) {
        // Here you would call your backend API to delete the instance
        showAlert(`Instance ${instanceId} deleted successfully`, 'success');
        // Refresh the page or remove the card from DOM
    }
}

// Add New Instance
function addNewInstance() {
    const form = document.getElementById('addInstanceForm');
    const formData = new FormData(form);
    
    // Here you would call your backend API to create the instance
    showAlert('New instance added successfully!', 'success');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addInstanceModal'));
    modal.hide();
    
    // Refresh the page or add new card to DOM
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Subscribe to Instance
function subscribeToInstance(instanceId) {
    // Here you would handle subscription logic
    showAlert('Subscription process started', 'info');
}

// Load Instances Data
function loadInstances() {
    // Here you would call your backend API to get instances
    // For now, we'll use dummy data
    const instances = [
        {
            id: 'instance1734',
            phone: '20111266019',
            label: 'Move Point',
            status: 'WORKING',
            messages: 5395,
            campaigns: 0,
            subscribedUntil: '1/25/2026',
            remaining: 0
        }
    ];
    
    return instances;
}

// Render Instances in Grid View
function renderGridView(instances) {
    const gridView = document.getElementById('gridView');
    
    // Clear existing cards (except the first one which is the template)
    while (gridView.children.length > 1) {
        gridView.removeChild(gridView.lastChild);
    }
    
    // Render each instance (skip the first one as it's already there)
    instances.slice(1).forEach(instance => {
        const card = createInstanceCard(instance);
        gridView.appendChild(card);
    });
}

// Create Instance Card Element
function createInstanceCard(instance) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6';
    
    col.innerHTML = `
        <div class="instance-card dashboard-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="instance-avatar">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">${instance.id}</h6>
                        <small class="text-muted">${instance.phone}</small>
                    </div>
                </div>
                <span class="badge bg-${instance.status === 'WORKING' ? 'success' : 'danger'}">
                    <i class="bi bi-${instance.status === 'WORKING' ? 'check-circle' : 'x-circle'} me-1"></i>
                    ${instance.status}
                </span>
            </div>

            <div class="instance-label mb-3">
                <i class="bi bi-tag me-1"></i>
                <span class="text-muted">${instance.label}</span>
            </div>

            <div class="alert alert-light border mb-3 py-2">
                <small class="d-block mb-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Anti
                </small>
                <small class="d-block mb-1">instances.subscribed_until: ${instance.subscribedUntil}</small>
                <small class="d-block">instances.remaining: ${instance.remaining}</small>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6 text-center">
                    <div class="instance-stat">
                        <h5 class="mb-0 text-primary fw-bold">${instance.messages}</h5>
                        <small class="text-muted">Messages</small>
                    </div>
                </div>
                <div class="col-6 text-center">
                    <div class="instance-stat">
                        <h5 class="mb-0 text-secondary fw-bold">${instance.campaigns}</h5>
                        <small class="text-muted">Campaigns</small>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" onclick="subscribeToInstance('${instance.id}')">
                <i class="bi bi-check-circle me-2"></i>
                subscribe now
            </button>

            <div class="d-flex gap-2 justify-content-center message-platforms">
                <span>Message Platform:</span>
                <button class="btn btn-sm btn-light"><i class="bi bi-robot"></i></button>
                <button class="btn btn-sm btn-light"><i class="bi bi-chat"></i></button>
                <button class="btn btn-sm btn-light"><i class="bi bi-android2"></i></button>
                <button class="btn btn-sm btn-light"><i class="bi bi-whatsapp"></i></button>
            </div>

            <div class="instance-actions mt-3 pt-3 border-top">
                <button class="btn btn-sm btn-outline-primary" onclick="viewInstance('${instance.id}')">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="editInstance('${instance.id}')">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteInstance('${instance.id}')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    `;
    
    return col;
}

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Load and render instances
    const instances = loadInstances();
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Set active menu item
    initDashboard();
});