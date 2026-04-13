// ========================================
// Campaigns Functions
// ========================================

let campaigns = [];

// Create Campaign
function createCampaign() {
    const form = document.getElementById('createCampaignForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const campaign = {
        id: Date.now(),
        name: form.querySelector('input[type="text"]').value,
        instance: form.querySelector('select').value,
        phoneList: form.querySelectorAll('select')[1].value,
        message: form.querySelector('textarea').value,
        startDate: form.querySelectorAll('input[type="datetime-local"]')[0].value,
        endDate: form.querySelectorAll('input[type="datetime-local"]')[1].value,
        status: 'not_started',
        totalMessages: 0,
        sentMessages: 0,
        pendingMessages: 0,
        createdAt: new Date().toISOString()
    };
    
    campaigns.push(campaign);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('createCampaignModal'));
    modal.hide();
    
    // Reset form
    form.reset();
    
    // Show success message
    showAlert('Campaign created successfully!', 'success');
    
    // Update UI
    renderCampaigns();
    updateStats();
}

// Render Campaigns
function renderCampaigns(filter = 'all') {
    const campaignsList = document.getElementById('campaignsList');
    
    let filteredCampaigns = campaigns;
    if (filter !== 'all') {
        filteredCampaigns = campaigns.filter(c => c.status === filter);
    }
    
    if (filteredCampaigns.length === 0) {
        campaignsList.innerHTML = `
            <i class="bi bi-megaphone-fill fs-1 text-muted"></i>
            <p class="text-muted mt-3 mb-0">No campaigns found</p>
        `;
        campaignsList.className = 'empty-state';
    } else {
        campaignsList.className = '';
        campaignsList.innerHTML = filteredCampaigns.map(campaign => `
            <div class="campaign-item mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${campaign.name}</h6>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-phone me-1"></i> ${campaign.instance}
                            <span class="mx-2">|</span>
                            <i class="bi bi-clock me-1"></i> ${new Date(campaign.createdAt).toLocaleString()}
                        </p>
                        <div class="d-flex gap-3 mb-2">
                            <small>
                                <i class="bi bi-envelope me-1"></i>
                                Total: <strong>${campaign.totalMessages}</strong>
                            </small>
                            <small>
                                <i class="bi bi-send-check me-1 text-success"></i>
                                Sent: <strong>${campaign.sentMessages}</strong>
                            </small>
                            <small>
                                <i class="bi bi-hourglass-split me-1 text-warning"></i>
                                Pending: <strong>${campaign.pendingMessages}</strong>
                            </small>
                        </div>
                        <span class="badge ${getStatusBadgeClass(campaign.status)}">
                            ${campaign.status.replace('_', ' ').toUpperCase()}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewCampaign(${campaign.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="startCampaign(${campaign.id})" ${campaign.status !== 'not_started' ? 'disabled' : ''}>
                            <i class="bi bi-play"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="pauseCampaign(${campaign.id})" ${campaign.status !== 'active' ? 'disabled' : ''}>
                            <i class="bi bi-pause"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCampaign(${campaign.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

// Get Status Badge Class
function getStatusBadgeClass(status) {
    const statusClasses = {
        'not_started': 'bg-secondary',
        'scheduled': 'bg-info',
        'active': 'bg-success',
        'paused': 'bg-warning',
        'finished': 'bg-dark'
    };
    return statusClasses[status] || 'bg-secondary';
}

// View Campaign
function viewCampaign(campaignId) {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (campaign) {
        showAlert(`Viewing campaign: ${campaign.name}`, 'info');
        // Here you would redirect to campaign details page
    }
}

// Start Campaign
function startCampaign(campaignId) {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (campaign && campaign.status === 'not_started') {
        campaign.status = 'active';
        showAlert(`Campaign "${campaign.name}" started successfully!`, 'success');
        renderCampaigns();
        updateStats();
    }
}

// Pause Campaign
function pauseCampaign(campaignId) {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (campaign && campaign.status === 'active') {
        campaign.status = 'paused';
        showAlert(`Campaign "${campaign.name}" paused!`, 'warning');
        renderCampaigns();
        updateStats();
    }
}

// Delete Campaign
function deleteCampaign(campaignId) {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (campaign && confirm(`Are you sure you want to delete campaign "${campaign.name}"?`)) {
        campaigns = campaigns.filter(c => c.id !== campaignId);
        showAlert('Campaign deleted successfully!', 'success');
        renderCampaigns();
        updateStats();
    }
}

// Update Stats
function updateStats() {
    document.getElementById('totalCampaigns').textContent = campaigns.length;
    
    const totalMessages = campaigns.reduce((sum, c) => sum + c.totalMessages, 0);
    document.getElementById('totalMessages').textContent = totalMessages;
    
    const sentMessages = campaigns.reduce((sum, c) => sum + c.sentMessages, 0);
    document.getElementById('sentMessages').textContent = sentMessages;
    
    const pendingMessages = campaigns.reduce((sum, c) => sum + c.pendingMessages, 0);
    document.getElementById('pendingMessages').textContent = pendingMessages;
}

// Load Campaigns
function loadCampaigns() {
    // Here you would load from backend API
    renderCampaigns();
    updateStats();
}

// Initialize on Page Load
document.addEventListener('DOMContentLoaded', function() {
    loadCampaigns();
    
    // Add tab change listeners
    const tabs = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('data-bs-target');
            let filter = 'all';
            
            if (targetId === '#notStartedTab') filter = 'not_started';
            else if (targetId === '#scheduledTab') filter = 'scheduled';
            else if (targetId === '#activeTab') filter = 'active';
            else if (targetId === '#pausedTab') filter = 'paused';
            else if (targetId === '#finishedTab') filter = 'finished';
            
            renderCampaigns(filter);
        });
    });
});