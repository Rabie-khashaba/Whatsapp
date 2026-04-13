// ========================================
// Admin Plans Management
// ========================================

// Sample Plans Data (Replace with API calls)
let plans = [
    {
        id: 1,
        name: 'Basic',
        description: 'Perfect for small businesses',
        monthlyPrice: 9.99,
        yearlyPrice: 99.99,
        maxInstances: 2,
        maxMessages: 1000,
        maxCampaigns: 5,
        color: 'secondary',
        features: [
            '2 WhatsApp Instances',
            '1,000 Messages/Month',
            'Up to 5 Campaigns',
            'Basic Analytics',
            'Email Support'
        ],
        active: true,
        subscribers: 45
    },
    {
        id: 2,
        name: 'Pro',
        description: 'For growing businesses',
        monthlyPrice: 29.99,
        yearlyPrice: 299.99,
        maxInstances: 5,
        maxMessages: 5000,
        maxCampaigns: 20,
        color: 'primary',
        features: [
            '5 WhatsApp Instances',
            '5,000 Messages/Month',
            'Up to 20 Campaigns',
            'Advanced Analytics',
            'Priority Support',
            'API Access'
        ],
        active: true,
        subscribers: 78
    },
    {
        id: 3,
        name: 'Enterprise',
        description: 'For large organizations',
        monthlyPrice: 99.99,
        yearlyPrice: 999.99,
        maxInstances: 10,
        maxMessages: 20000,
        maxCampaigns: 100,
        color: 'warning',
        features: [
            '10 WhatsApp Instances',
            '20,000 Messages/Month',
            'Unlimited Campaigns',
            'Advanced Analytics & Reports',
            '24/7 Priority Support',
            'Full API Access',
            'Custom Integration',
            'Dedicated Account Manager'
        ],
        active: true,
        subscribers: 33
    }
];

// ========================================
// Initialize Page
// ========================================
function initPlansPage() {
    // Check authentication
    checkAdminAuth();
    
    // Display plans
    displayPlans();
    
    // Apply saved language
    loadSavedLanguage();
}

// ========================================
// Display Plans
// ========================================
function displayPlans() {
    const container = document.getElementById('plansContainer');
    container.innerHTML = '';
    
    // Add "Add New Plan" card first
    const addCard = createAddPlanCard();
    container.appendChild(addCard);
    
    // Display existing plans
    plans.forEach(plan => {
        const planCard = createPlanCard(plan);
        container.appendChild(planCard);
    });
}

// ========================================
// Create Add Plan Card
// ========================================
function createAddPlanCard() {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6';
    col.innerHTML = `
        <div class="dashboard-card text-center" style="min-height: 600px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed var(--border-color);" onclick="openAddPlanModal()">
            <div>
                <i class="bi bi-plus-circle text-primary" style="font-size: 64px;"></i>
                <h5 class="mt-3" data-en="Add New Plan" data-ar="إضافة باقة جديدة">Add New Plan</h5>
                <p class="text-muted" data-en="Create a new subscription plan" data-ar="إنشاء باقة اشتراك جديدة">Create a new subscription plan</p>
            </div>
        </div>
    `;
    return col;
}

// ========================================
// Create Plan Card
// ========================================
function createPlanCard(plan) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6';
    
    const featuresHTML = plan.features.map(feature => 
        `<li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>${feature}</li>`
    ).join('');
    
    col.innerHTML = `
        <div class="dashboard-card position-relative" style="min-height: 600px;">
            ${!plan.active ? '<div class="position-absolute top-0 end-0 m-3"><span class="badge bg-secondary">Inactive</span></div>' : ''}
            
            <div class="text-center mb-4">
                <span class="badge bg-${plan.color} mb-2 px-3 py-2">${plan.name.toUpperCase()}</span>
                <h5 class="text-muted mb-3">${plan.description}</h5>
                
                <div class="mb-3">
                    <h2 class="fw-bold mb-0">$${plan.monthlyPrice.toFixed(2)}<small class="text-muted fs-6">/month</small></h2>
                    <small class="text-muted">or $${plan.yearlyPrice.toFixed(2)}/year</small>
                </div>
                
                <div class="alert alert-light border">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="fw-bold text-primary">${plan.maxInstances}</div>
                            <small class="text-muted">Instances</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-primary">${plan.subscribers}</div>
                            <small class="text-muted">Subscribers</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <ul class="list-unstyled mb-4">
                ${featuresHTML}
            </ul>
            
            <div class="d-grid gap-2 mt-auto">
                <button class="btn btn-outline-primary" onclick="editPlan(${plan.id})">
                    <i class="bi bi-pencil me-2"></i>
                    <span data-en="Edit Plan" data-ar="تعديل الباقة">Edit Plan</span>
                </button>
                <button class="btn btn-outline-${plan.active ? 'warning' : 'success'}" onclick="togglePlanStatus(${plan.id})">
                    <i class="bi bi-${plan.active ? 'pause' : 'play'}-circle me-2"></i>
                    <span data-en="${plan.active ? 'Deactivate' : 'Activate'}" data-ar="${plan.active ? 'تعطيل' : 'تفعيل'}">${plan.active ? 'Deactivate' : 'Activate'}</span>
                </button>
                ${plan.subscribers === 0 ? `
                    <button class="btn btn-outline-danger" onclick="deletePlan(${plan.id})">
                        <i class="bi bi-trash me-2"></i>
                        <span data-en="Delete Plan" data-ar="حذف الباقة">Delete Plan</span>
                    </button>
                ` : ''}
            </div>
        </div>
    `;
    
    return col;
}

// ========================================
// Open Add Plan Modal
// ========================================
function openAddPlanModal() {
    document.getElementById('planModalTitle').textContent = 'Add New Plan';
    document.getElementById('planId').value = '';
    document.getElementById('planForm').reset();
    document.getElementById('planActive').checked = true;
    
    const modal = new bootstrap.Modal(document.getElementById('planModal'));
    modal.show();
}

// ========================================
// Edit Plan
// ========================================
function editPlan(id) {
    const plan = plans.find(p => p.id === id);
    if (!plan) return;
    
    document.getElementById('planModalTitle').textContent = 'Edit Plan';
    document.getElementById('planId').value = plan.id;
    document.getElementById('planName').value = plan.name;
    document.getElementById('planDescription').value = plan.description;
    document.getElementById('monthlyPrice').value = plan.monthlyPrice;
    document.getElementById('yearlyPrice').value = plan.yearlyPrice;
    document.getElementById('maxInstances').value = plan.maxInstances;
    document.getElementById('maxMessages').value = plan.maxMessages;
    document.getElementById('maxCampaigns').value = plan.maxCampaigns;
    document.getElementById('planColor').value = plan.color;
    document.getElementById('planFeatures').value = plan.features.join('\n');
    document.getElementById('planActive').checked = plan.active;
    
    const modal = new bootstrap.Modal(document.getElementById('planModal'));
    modal.show();
}

// ========================================
// Save Plan
// ========================================
function savePlan() {
    const id = document.getElementById('planId').value;
    const name = document.getElementById('planName').value;
    const description = document.getElementById('planDescription').value;
    const monthlyPrice = parseFloat(document.getElementById('monthlyPrice').value);
    const yearlyPrice = parseFloat(document.getElementById('yearlyPrice').value);
    const maxInstances = parseInt(document.getElementById('maxInstances').value);
    const maxMessages = parseInt(document.getElementById('maxMessages').value);
    const maxCampaigns = parseInt(document.getElementById('maxCampaigns').value);
    const color = document.getElementById('planColor').value;
    const featuresText = document.getElementById('planFeatures').value;
    const active = document.getElementById('planActive').checked;
    
    if (!name || !monthlyPrice || !yearlyPrice || !maxInstances) {
        alert('Please fill in all required fields');
        return;
    }
    
    const features = featuresText.split('\n').filter(f => f.trim() !== '');
    
    if (id) {
        // Update existing plan
        const planIndex = plans.findIndex(p => p.id == id);
        if (planIndex !== -1) {
            plans[planIndex] = {
                ...plans[planIndex],
                name,
                description,
                monthlyPrice,
                yearlyPrice,
                maxInstances,
                maxMessages,
                maxCampaigns,
                color,
                features,
                active
            };
            showAlert('Plan updated successfully!', 'success');
        }
    } else {
        // Add new plan
        const newPlan = {
            id: plans.length + 1,
            name,
            description,
            monthlyPrice,
            yearlyPrice,
            maxInstances,
            maxMessages,
            maxCampaigns,
            color,
            features,
            active,
            subscribers: 0
        };
        plans.push(newPlan);
        showAlert('Plan added successfully!', 'success');
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('planModal'));
    modal.hide();
    
    // Refresh display
    displayPlans();
}

// ========================================
// Toggle Plan Status
// ========================================
function togglePlanStatus(id) {
    const plan = plans.find(p => p.id === id);
    if (!plan) return;
    
    const action = plan.active ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this plan?`)) return;
    
    plan.active = !plan.active;
    displayPlans();
    
    showAlert(`Plan ${action}d successfully!`, 'success');
}

// ========================================
// Delete Plan
// ========================================
function deletePlan(id) {
    const plan = plans.find(p => p.id === id);
    if (!plan) return;
    
    if (plan.subscribers > 0) {
        alert('Cannot delete plan with active subscribers!');
        return;
    }
    
    if (!confirm('Are you sure you want to delete this plan? This action cannot be undone.')) return;
    
    plans = plans.filter(p => p.id !== id);
    displayPlans();
    
    showAlert('Plan deleted successfully!', 'success');
}

// ========================================
// Helper Functions
// ========================================
function checkAdminAuth() {
    const adminToken = sessionStorage.getItem('adminToken');
    if (!adminToken) {
        window.location.href = 'admin-login.html';
    }
}

function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('adminToken');
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
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// ========================================
// Initialize on Page Load
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initPlansPage();
});