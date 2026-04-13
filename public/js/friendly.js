// ========================================
// Friendly Messages & Contacts Functions
// ========================================

// Store contacts and messages
let contacts = [];
let messages = [];

// Add Contact
function addContact(event) {
    event.preventDefault();
    
    const form = event.target;
    const phone = form.querySelector('input[type="tel"]').value;
    const note = form.querySelector('textarea').value;
    
    // Validate phone
    if (!phone || phone.length < 10) {
        showAlert('Please enter a valid phone number', 'danger');
        return;
    }
    
    // Check if contact already exists
    const exists = contacts.find(c => c.phone === phone);
    if (exists) {
        showAlert('Contact already exists!', 'warning');
        return;
    }
    
    // Add contact
    const contact = {
        id: Date.now(),
        phone,
        note,
        addedAt: new Date().toISOString()
    };
    
    contacts.push(contact);
    
    // Show success message
    showAlert('Contact added successfully!', 'success');
    
    // Reset form
    form.reset();
    
    // Update UI
    renderContacts();
    updateContactsCount();
}

// Render Contacts List
function renderContacts() {
    const contactsList = document.getElementById('existingContactsList');
    
    if (contacts.length === 0) {
        contactsList.innerHTML = `
            <i class="bi bi-person-x fs-3 text-muted"></i>
            <p class="text-muted mt-2">No contacts added yet</p>
        `;
        contactsList.className = 'empty-state';
    } else {
        contactsList.className = '';
        contactsList.innerHTML = contacts.map(contact => `
            <div class="contact-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">
                            <i class="bi bi-phone me-2 text-primary"></i>
                            ${contact.phone}
                        </div>
                        ${contact.note ? `<small class="text-muted d-block mt-1">${contact.note}</small>` : ''}
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-clock me-1"></i>
                            ${new Date(contact.addedAt).toLocaleString()}
                        </small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(${contact.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Delete Contact
function deleteContact(contactId) {
    if (confirm('Are you sure you want to delete this contact?')) {
        contacts = contacts.filter(c => c.id !== contactId);
        renderContacts();
        updateContactsCount();
        showAlert('Contact deleted successfully!', 'success');
    }
}

// Update Contacts Count
function updateContactsCount() {
    const countElement = document.querySelector('h6[data-en*="Existing Contacts"]');
    if (countElement) {
        const enText = `Existing Contacts (${contacts.length})`;
        const arText = `جهات الاتصال الحالية (${contacts.length})`;
        countElement.setAttribute('data-en', enText);
        countElement.setAttribute('data-ar', arText);
        countElement.textContent = currentLanguage === 'ar' ? arText : enText;
    }
    
    // Update stats card
    const statsCard = document.querySelector('.stats-card-sm h4');
    if (statsCard) {
        statsCard.textContent = contacts.length;
    }
}

// Add Message
function addMessage() {
    const messageText = document.querySelector('#addMessageForm textarea').value;
    const tagsInput = document.querySelector('#addMessageForm input[type="text"]').value;
    
    if (!messageText.trim()) {
        showAlert('Please enter a message', 'danger');
        return;
    }
    
    const message = {
        id: Date.now(),
        text: messageText,
        tags: tagsInput ? tagsInput.split(',').map(t => t.trim()) : [],
        addedAt: new Date().toISOString()
    };
    
    messages.push(message);
    
    showAlert('Message added successfully!', 'success');
    
    // Reset form
    document.querySelector('#addMessageForm').reset();
    
    // Update UI
    renderMessages();
    updateMessagesCount();
}

// Handle Add Message Form
function handleAddMessage(event) {
    event.preventDefault();
    addMessage();
}

// Render Messages List
function renderMessages() {
    const messagesList = document.getElementById('existingMessagesList');
    
    if (messages.length === 0) {
        messagesList.innerHTML = `
            <i class="bi bi-chat-x fs-3 text-muted"></i>
            <p class="text-muted mt-2">No messages added yet</p>
        `;
        messagesList.className = 'empty-state';
    } else {
        messagesList.className = '';
        messagesList.innerHTML = messages.map(message => `
            <div class="message-item">
                <div class="message-text">
                    <p class="mb-2">${message.text}</p>
                    ${message.tags.length > 0 ? `
                        <div class="mb-2">
                            ${message.tags.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('')}
                        </div>
                    ` : ''}
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        ${new Date(message.addedAt).toLocaleString()}
                    </small>
                </div>
                <div class="message-actions">
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteMessage(${message.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Delete Message
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        messages = messages.filter(m => m.id !== messageId);
        renderMessages();
        updateMessagesCount();
        showAlert('Message deleted successfully!', 'success');
    }
}

// Update Messages Count
function updateMessagesCount() {
    const countElement = document.querySelector('h6[data-en*="Existing Messages"]');
    if (countElement) {
        const enText = `Existing Messages (${messages.length})`;
        const arText = `الرسائل الحالية (${messages.length})`;
        countElement.setAttribute('data-en', enText);
        countElement.setAttribute('data-ar', arText);
        countElement.textContent = currentLanguage === 'ar' ? arText : enText;
    }
    
    // Update stats card
    const statsCards = document.querySelectorAll('.stats-card-sm h4');
    if (statsCards.length > 1) {
        statsCards[1].textContent = messages.length;
    }
}

// Load Data from Storage (if using localStorage later)
function loadFriendlyData() {
    // Here you would load from backend API or localStorage
    // For now, we start with empty arrays
    renderContacts();
    renderMessages();
}

// Initialize on Page Load
document.addEventListener('DOMContentLoaded', function() {
    loadFriendlyData();
});