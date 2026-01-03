/**
 * Admin Panel JavaScript
 * Horse Racing Platform
 */

// =====================================================
// INITIALIZATION
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initDropdowns();
    initModals();
    initTabs();
    initForms();
    initDataTables();
    initTooltips();
    initConfirmActions();
    initLiveTime();
});

// =====================================================
// MOBILE MENU
// =====================================================

function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar, aside');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            if (overlay) {
                overlay.classList.toggle('active');
            }
            document.body.classList.toggle('sidebar-open');
        });
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            });
        }
    }
}

// =====================================================
// DROPDOWNS
// =====================================================

function initDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.closest('.dropdown');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown.active').forEach(d => {
                if (d !== dropdown) d.classList.remove('active');
            });
            
            dropdown.classList.toggle('active');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown.active').forEach(d => {
            d.classList.remove('active');
        });
    });
}

// =====================================================
// MODALS
// =====================================================

function initModals() {
    // Open modal buttons
    document.querySelectorAll('[data-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.dataset.modal;
            openModal(modalId);
        });
    });
    
    // Close modal buttons
    document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            closeModal(modal.id);
        });
    });
    
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }
}

// =====================================================
// TABS
// =====================================================

function initTabs() {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabGroup = this.closest('.tabs');
            const contentGroup = tabGroup.nextElementSibling;
            const targetId = this.dataset.tab;
            
            // Update tab states
            tabGroup.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update content states
            if (contentGroup) {
                contentGroup.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                const targetContent = document.getElementById(targetId + '-tab');
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                    targetContent.classList.add('active');
                }
            }
        });
    });
}

// =====================================================
// FORMS
// =====================================================

function initForms() {
    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Input formatting
    document.querySelectorAll('input[data-format="currency"]').forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^0-9.-]/g, ''));
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    });
    
    // Character counter
    document.querySelectorAll('textarea[data-maxlength]').forEach(textarea => {
        const maxLength = parseInt(textarea.dataset.maxlength);
        const counter = document.createElement('div');
        counter.className = 'text-xs text-gray-500 mt-1 text-right';
        counter.textContent = `0 / ${maxLength}`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = `${length} / ${maxLength}`;
            counter.classList.toggle('text-red-500', length > maxLength);
        });
    });
    
    // File upload preview
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    
    // Clear previous errors
    form.querySelectorAll('.form-error').forEach(el => el.remove());
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    
    // Check required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showFieldError(field, 'This field is required');
        }
    });
    
    // Check email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            isValid = false;
            showFieldError(field, 'Please enter a valid email address');
        }
    });
    
    // Check min length
    form.querySelectorAll('[minlength]').forEach(field => {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (field.value.length < minLength) {
            isValid = false;
            showFieldError(field, `Minimum ${minLength} characters required`);
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// =====================================================
// DATA TABLES
// =====================================================

function initDataTables() {
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }
    
    // Row checkboxes
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    
    // Sortable columns
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const direction = this.classList.contains('sort-asc') ? 'desc' : 'asc';
            
            // Update header classes
            table.querySelectorAll('th[data-sort]').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add(`sort-${direction}`);
            
            // Sort rows
            rows.sort((a, b) => {
                const aVal = a.querySelector(`td:nth-child(${getColumnIndex(th)})`).textContent.trim();
                const bVal = b.querySelector(`td:nth-child(${getColumnIndex(th)})`).textContent.trim();
                
                if (direction === 'asc') {
                    return aVal.localeCompare(bVal, undefined, { numeric: true });
                } else {
                    return bVal.localeCompare(aVal, undefined, { numeric: true });
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}

function getColumnIndex(th) {
    return Array.from(th.parentNode.children).indexOf(th) + 1;
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (bulkActions) {
        if (checked.length > 0) {
            bulkActions.classList.remove('hidden');
            if (selectedCount) {
                selectedCount.textContent = checked.length;
            }
        } else {
            bulkActions.classList.add('hidden');
        }
    }
    
    // Update select all checkbox state
    const selectAll = document.getElementById('select-all');
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    if (selectAll && allCheckboxes.length > 0) {
        selectAll.checked = checked.length === allCheckboxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < allCheckboxes.length;
    }
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value));
}

// =====================================================
// TOOLTIPS
// =====================================================

function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function() {
            const text = this.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: #1f2937;
                color: white;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                z-index: 100;
                pointer-events: none;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8 + window.scrollY) + 'px';
            tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
            
            this._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// =====================================================
// CONFIRM ACTIONS
// =====================================================

function initConfirmActions() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            
            if (typeof Swal !== 'undefined') {
                e.preventDefault();
                Swal.fire({
                    title: 'Confirm Action',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (this.tagName === 'A') {
                            window.location.href = this.href;
                        } else if (this.form) {
                            this.form.submit();
                        }
                    }
                });
            } else if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

// =====================================================
// DELETE ITEM FUNCTION
// =====================================================

function deleteItem(type, id, name) {
    const title = name ? `Delete "${name}"?` : 'Delete this item?';
    
    Swal.fire({
        title: title,
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('id', id);
            
            fetch(ADMIN_URL + '/ajax/delete-item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Remove row from table or reload page
                        const row = document.getElementById(`${type}-row-${id}`) || 
                                   document.getElementById(`entry-row-${id}`);
                        if (row) {
                            row.remove();
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            });
        }
    });
}

// =====================================================
// BULK ACTIONS
// =====================================================

function bulkAction(action, type) {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        Swal.fire('Warning', 'Please select at least one item.', 'warning');
        return;
    }
    
    const actionNames = {
        'delete': 'delete',
        'activate': 'activate',
        'deactivate': 'deactivate',
        'feature': 'feature',
        'unfeature': 'unfeature'
    };
    
    Swal.fire({
        title: 'Confirm Bulk Action',
        text: `This will ${actionNames[action] || action} ${ids.length} item(s).`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#ef4444' : '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(ADMIN_URL + '/ajax/bulk-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, type: type, ids: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            });
        }
    });
}

// =====================================================
// LIVE TIME UPDATE
// =====================================================

function initLiveTime() {
    const liveTimeElements = document.querySelectorAll('.live-time');
    
    if (liveTimeElements.length > 0) {
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            liveTimeElements.forEach(el => {
                el.textContent = timeString;
            });
        }
        
        updateTime();
        setInterval(updateTime, 1000);
    }
}

// =====================================================
// NOTIFICATIONS
// =====================================================

function showNotification(type, title, message, duration = 3000) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true
        });
    } else {
        alert(`${title}: ${message}`);
    }
}

function showSuccess(message) {
    showNotification('success', 'Success', message);
}

function showError(message) {
    showNotification('error', 'Error', message);
}

function showWarning(message) {
    showNotification('warning', 'Warning', message);
}

function showInfo(message) {
    showNotification('info', 'Info', message);
}

// =====================================================
// AJAX HELPERS
// =====================================================

async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {}
    };
    
    if (data) {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Request Error:', error);
        return { success: false, error: 'Network error occurred' };
    }
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

function formatCurrency(amount, currency = 'USD') {
    const symbols = { 'USD': '$', 'GBP': '£', 'EUR': '€', 'AUD': 'A$' };
    const symbol = symbols[currency] || '$';
    return symbol + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// =====================================================
// COPY TO CLIPBOARD
// =====================================================

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showError('Failed to copy to clipboard');
    });
}

// =====================================================
// EXPORT FUNCTIONS (Global)
// =====================================================

window.openModal = openModal;
window.closeModal = closeModal;
window.deleteItem = deleteItem;
window.bulkAction = bulkAction;
window.showNotification = showNotification;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;
window.apiRequest = apiRequest;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatTime = formatTime;
window.copyToClipboard = copyToClipboard;
window.getSelectedIds = getSelectedIds;