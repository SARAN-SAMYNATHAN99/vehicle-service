/**
 * GearShift - Core API & UI Helpers
 * Pure Vanilla JavaScript
 */

const API_BASE = (function() {
    // If inside the /admin/ folder, backend is at ../backend; otherwise it is at backend
    const path = window.location.pathname;
    if (path.includes('/admin/')) {
        return '../backend';
    }
    return 'backend';
})();

/**
 * Perform Fetch request to backend with error handling and JSON parsing
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE}/${endpoint}`;
    const options = {
        method: method,
        headers: {
            'Accept': 'application/json'
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        // If session expired and trying to access protected endpoint
        if (response.status === 401 && !endpoint.includes('auth/me.php') && !endpoint.includes('auth/login.php')) {
            showToast('error', 'Session Expired', 'Please login to continue.');
            setTimeout(() => {
                const isAdmin = window.location.pathname.includes('/admin/');
                window.location.href = isAdmin ? 'index.html' : 'login.html';
            }, 1200);
            return result;
        }

        return result;
    } catch (err) {
        console.error(`API Error on [${method}] ${url}:`, err);
        return {
            success: false,
            message: 'Unable to communicate with the server. Please check your XAMPP Apache/MySQL connection.'
        };
    }
}

/**
 * Toast Notification System
 */
function showToast(type = 'info', title = '', message = '') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || 'ℹ'}</div>
        <div class="toast-content">
            <div class="toast-title">${title || capitalize(type)}</div>
            <p class="toast-message">${message}</p>
        </div>
    `;

    container.appendChild(toast);

    // Trigger animate in
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    // Remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Modal System Helpers
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Global modal backdrop close listener
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

/**
 * Interactive Confirmation Dialog Modal
 */
function showConfirm(title, message, onConfirm, confirmText = 'Confirm', confirmClass = 'btn-danger') {
    let confirmModal = document.getElementById('global-confirm-modal');
    if (!confirmModal) {
        confirmModal = document.createElement('div');
        confirmModal.id = 'global-confirm-modal';
        confirmModal.className = 'modal-backdrop';
        confirmModal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title" id="confirm-modal-title">Confirm Action</h3>
                    <button class="modal-close" onclick="closeModal('global-confirm-modal')">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="confirm-modal-message" style="margin:0; font-size:1rem;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" onclick="closeModal('global-confirm-modal')">Cancel</button>
                    <button type="button" class="btn" id="confirm-modal-btn">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(confirmModal);
    }

    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-message').textContent = message;
    
    const confirmBtn = document.getElementById('confirm-modal-btn');
    confirmBtn.className = `btn ${confirmClass}`;
    confirmBtn.textContent = confirmText;

    // Replace click handler cleanly
    const newBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

    newBtn.addEventListener('click', async () => {
        closeModal('global-confirm-modal');
        if (typeof onConfirm === 'function') {
            await onConfirm();
        }
    });

    openModal('global-confirm-modal');
}

/**
 * Format Currency ($XX.XX)
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

/**
 * Format Date (e.g., Oct 12, 2026)
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date)) return dateString;
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
