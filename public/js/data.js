// ========================================
// Phone Lists Functions
// ========================================

let phoneLists = [];

// Create Phone List
function createPhoneList() {
    const listName = document.getElementById('listName').value;
    const listDescription = document.getElementById('listDescription').value;
    const phoneFile = document.getElementById('phoneFile').files[0];
    const manualPhones = document.getElementById('manualPhones').value;
    
    if (!listName) {
        showAlert('Please enter a list name', 'danger');
        return;
    }
    
    let phones = [];
    
    // If manual phones are entered
    if (manualPhones.trim()) {
        phones = manualPhones.trim().split('\n').map(p => p.trim()).filter(p => p);
    }
    
    // If file is uploaded
    if (phoneFile) {
        showAlert('Processing file...', 'info');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const content = e.target.result;
            const filePhones = content.split('\n').map(p => p.trim()).filter(p => p);
            phones = [...phones, ...filePhones];
            
            savePhoneList(listName, listDescription, phones);
        };
        reader.readAsText(phoneFile);
    } else if (phones.length > 0) {
        savePhoneList(listName, listDescription, phones);
    } else {
        showAlert('Please upload a file or enter phone numbers manually', 'danger');
    }
}

// Save Phone List
function savePhoneList(name, description, phones) {
    // Validate and clean phone numbers
    const validPhones = [];
    const invalidPhones = [];
    
    phones.forEach(phone => {
        // Remove all non-digit characters
        const cleanPhone = phone.replace(/\D/g, '');
        
        // Check if valid (10-15 digits)
        if (cleanPhone.length >= 10 && cleanPhone.length <= 15) {
            validPhones.push(cleanPhone);
        } else {
            invalidPhones.push(phone);
        }
    });
    
    const phoneList = {
        id: Date.now(),
        name,
        description,
        phones: validPhones,
        invalidPhones: invalidPhones.length,
        totalNumbers: validPhones.length,
        createdAt: new Date().toISOString()
    };
    
    phoneLists.push(phoneList);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('createListModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('createListForm').reset();
    
    // Show success message
    showAlert(`Phone list created successfully! ${validPhones.length} valid numbers, ${invalidPhones.length} invalid numbers`, 'success');
    
    // Update UI
    renderPhoneLists();
}

// Render Phone Lists
function renderPhoneLists() {
    const tableBody = document.getElementById('phoneListsTable');
    
    if (phoneLists.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="empty-state-inline">
                        <i class="bi bi-telephone-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No phone lists found</p>
                        <p class="text-muted small">Create your first phone list to get started</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        tableBody.innerHTML = phoneLists.map(list => `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input">
                </td>
                <td>
                    <strong>${list.name}</strong>
                </td>
                <td>
                    <span class="text-muted">${list.description || '-'}</span>
                </td>
                <td>
                    <span class="badge bg-primary">${list.totalNumbers}</span>
                </td>
                <td>
                    <span class="badge bg-${list.invalidPhones > 0 ? 'danger' : 'success'}">${list.invalidPhones}</span>
                </td>
                <td>
                    <small class="text-muted">${new Date(list.createdAt).toLocaleDateString()}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewList(${list.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="downloadList(${list.id})" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="editList(${list.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteList(${list.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
}

// View List
function viewList(listId) {
    const list = phoneLists.find(l => l.id === listId);
    if (!list) return;
    
    const modal = new bootstrap.Modal(document.getElementById('viewListModal'));
    document.getElementById('viewListTitle').textContent = list.name;
    
    const content = `
        <div class="mb-3">
            <strong>Description:</strong> ${list.description || 'No description'}
        </div>
        <div class="mb-3">
            <strong>Total Numbers:</strong> ${list.totalNumbers}
        </div>
        <div class="mb-3">
            <strong>Invalid Numbers:</strong> ${list.invalidPhones}
        </div>
        <div class="mb-3">
            <strong>Created:</strong> ${new Date(list.createdAt).toLocaleString()}
        </div>
        <div class="mb-3">
            <strong>Phone Numbers (First 10):</strong>
            <div class="mt-2 p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                ${list.phones.slice(0, 10).map(p => `<div class="mb-1">${p}</div>`).join('')}
                ${list.phones.length > 10 ? `<div class="text-muted mt-2">... and ${list.phones.length - 10} more</div>` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('listDetailsContent').innerHTML = content;
    modal.show();
}

// Download List
function downloadList(listId) {
    const list = phoneLists.find(l => l.id === listId);
    if (!list) return;
    
    // Create CSV content
    const csvContent = 'Phone Number\n' + list.phones.join('\n');
    
    // Create blob and download
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${list.name}_${Date.now()}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Phone list downloaded successfully!', 'success');
}

// Edit List
function editList(listId) {
    const list = phoneLists.find(l => l.id === listId);
    if (list) {
        showAlert('Edit functionality coming soon!', 'info');
        // Here you would open edit modal
    }
}

// Delete List
function deleteList(listId) {
    const list = phoneLists.find(l => l.id === listId);
    if (list && confirm(`Are you sure you want to delete "${list.name}"?`)) {
        phoneLists = phoneLists.filter(l => l.id !== listId);
        renderPhoneLists();
        showAlert('Phone list deleted successfully!', 'success');
    }
}

// Download Sample File
function downloadSample() {
    const sampleContent = 'Phone Number\n201001234567\n201007654321\n201009876543';
    const blob = new Blob([sampleContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample_phone_list.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Sample file downloaded!', 'success');
}

// Export List (from view modal)
function exportList() {
    showAlert('Export functionality coming soon!', 'info');
}

// Select All Checkbox
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#phoneListsTable input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
    
    // Load phone lists
    renderPhoneLists();
});