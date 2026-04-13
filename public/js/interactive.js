// ========================================
// Interactive Messages Functions
// ========================================

let interactiveMessages = [];

// Create New Message
function createNewMessage() {
    const modal = new bootstrap.Modal(document.getElementById('createInteractiveModal'));
    modal.show();
}

// Add Section
function addSection() {
    const container = document.getElementById('sectionsContainer');
    const sectionCount = container.children.length + 1;
    
    const sectionDiv = document.createElement('div');
    sectionDiv.className = 'section-item mb-3 p-3 border rounded';
    sectionDiv.innerHTML = `
        <div class="mb-2">
            <label class="form-label small">Section Title</label>
            <input type="text" class="form-control form-control-sm" placeholder="Section ${sectionCount}">
        </div>
        <div class="mb-2">
            <label class="form-label small">Rows (One per line)</label>
            <textarea class="form-control form-control-sm" rows="3" placeholder="Row 1&#10;Row 2&#10;Row 3"></textarea>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection(this)">
            <i class="bi bi-trash"></i> Remove Section
        </button>
    `;
    
    container.appendChild(sectionDiv);
}

// Remove Section
function removeSection(button) {
    button.closest('.section-item').remove();
}

// Update Preview
function updatePreview() {
    const title = document.getElementById('messageTitle').value || 'Message Title';
    const body = document.getElementById('bodyText').value || 'Body text will appear here';
    const footer = document.getElementById('footerText').value || 'Footer text';
    const buttonText = document.getElementById('buttonText').value || 'View Options';
    
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewBody').textContent = body;
    document.getElementById('previewFooter').textContent = footer;
    document.getElementById('previewButtonText').textContent = buttonText;
}

// Save Interactive Message
function saveInteractiveMessage() {
    const title = document.getElementById('messageTitle').value;
    const body = document.getElementById('bodyText').value;
    const footer = document.getElementById('footerText').value;
    const buttonText = document.getElementById('buttonText').value;
    
    if (!title || !body) {
        showAlert('Please fill in required fields', 'danger');
        return;
    }
    
    // Get sections
    const sections = [];
    const sectionItems = document.querySelectorAll('.section-item');
    
    sectionItems.forEach(item => {
        const sectionTitle = item.querySelector('input').value;
        const rowsText = item.querySelector('textarea').value;
        const rows = rowsText.split('\n').filter(r => r.trim());
        
        if (sectionTitle && rows.length > 0) {
            sections.push({
                title: sectionTitle,
                rows: rows
            });
        }
    });
    
    const message = {
        id: Date.now(),
        title,
        body,
        footer,
        buttonText,
        sections,
        createdAt: new Date().toISOString()
    };
    
    interactiveMessages.push(message);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('createInteractiveModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('interactiveMessageForm').reset();
    document.getElementById('sectionsContainer').innerHTML = `
        <div class="section-item mb-3 p-3 border rounded">
            <div class="mb-2">
                <label class="form-label small">Section Title</label>
                <input type="text" class="form-control form-control-sm" placeholder="Section 1">
            </div>
            <div class="mb-2">
                <label class="form-label small">Rows (One per line)</label>
                <textarea class="form-control form-control-sm" rows="3" placeholder="Row 1&#10;Row 2&#10;Row 3"></textarea>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection(this)">
                <i class="bi bi-trash"></i> Remove Section
            </button>
        </div>
    `;
    
    // Show success
    showAlert('Interactive message created successfully!', 'success');
    
    // Update UI
    renderInteractiveMessages();
}

// Render Interactive Messages
function renderInteractiveMessages() {
    const container = document.getElementById('interactiveMessagesList');
    
    if (interactiveMessages.length === 0) {
        container.innerHTML = `
            <i class="bi bi-chat-square-text fs-1 text-muted"></i>
            <p class="text-muted mt-3 mb-2 fw-semibold">No interactive messages yet</p>
            <p class="text-muted small mb-3">Get started by creating your first interactive list message</p>
            <button class="btn btn-primary" onclick="createNewMessage()">
                <i class="bi bi-plus-circle me-2"></i>
                Create First Message
            </button>
        `;
        container.className = 'empty-state';
    } else {
        container.className = 'row g-3';
        container.innerHTML = interactiveMessages.map(msg => `
            <div class="col-md-6 col-lg-4">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0 fw-bold">${msg.title}</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editMessage(${msg.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="duplicateMessage(${msg.id})">
                                    <i class="bi bi-files me-2"></i>Duplicate
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="previewMessage(${msg.id})">
                                    <i class="bi bi-eye me-2"></i>Preview
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteMessage(${msg.id})">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">${msg.body.substring(0, 100)}${msg.body.length > 100 ? '...' : ''}</p>
                    <div class="mb-2">
                        <span class="badge bg-secondary me-1">${msg.sections.length} Sections</span>
                        <span class="badge bg-info">${msg.sections.reduce((sum, s) => sum + s.rows.length, 0)} Rows</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        ${new Date(msg.createdAt).toLocaleString()}
                    </small>
                </div>
            </div>
        `).join('');
    }
}

// Edit Message
function editMessage(messageId) {
    showAlert('Edit functionality coming soon!', 'info');
}

// Duplicate Message
function duplicateMessage(messageId) {
    const message = interactiveMessages.find(m => m.id === messageId);
    if (message) {
        const duplicate = {
            ...message,
            id: Date.now(),
            title: message.title + ' (Copy)',
            createdAt: new Date().toISOString()
        };
        interactiveMessages.push(duplicate);
        renderInteractiveMessages();
        showAlert('Message duplicated successfully!', 'success');
    }
}

// Preview Message
function previewMessage(messageId) {
    const message = interactiveMessages.find(m => m.id === messageId);
    if (message) {
        showAlert(`Previewing: ${message.title}`, 'info');
    }
}

// Delete Message
function deleteMessage(messageId) {
    const message = interactiveMessages.find(m => m.id === messageId);
    if (message && confirm(`Delete "${message.title}"?`)) {
        interactiveMessages = interactiveMessages.filter(m => m.id !== messageId);
        renderInteractiveMessages();
        showAlert('Message deleted successfully!', 'success');
    }
}

// View Archived
function viewArchived() {
    showAlert('Archived messages coming soon!', 'info');
}

// Initialize on Page Load
document.addEventListener('DOMContentLoaded', function() {
    renderInteractiveMessages();
    
    // Add input listeners for preview
    const inputs = ['messageTitle', 'bodyText', 'footerText', 'buttonText'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updatePreview);
        }
    });
});