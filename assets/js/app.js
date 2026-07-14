// ============================================================================
// LOADING SPINNER UTILITIES
// ============================================================================

const LoadingSpinner = {
    show: function() {
        let spinner = document.getElementById('globalSpinner');
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.id = 'globalSpinner';
            spinner.className = 'global-spinner';
            spinner.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
            document.body.appendChild(spinner);
        }
        spinner.classList.add('show');
        document.body.style.overflow = 'hidden';
    },
    hide: function() {
        const spinner = document.getElementById('globalSpinner');
        if (spinner) {
            spinner.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
};

// ============================================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================================

const Toast = {
    show: function(message, type = 'info', duration = 5000) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toastId = 'toast-' + Date.now();
        const typeClass = ['success', 'danger', 'warning', 'info'].includes(type) ? type : 'info';
        const iconClass = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-exclamation-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        }[typeClass];

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast-message toast-${typeClass}`;
        toast.innerHTML = `
            <i class="bi ${iconClass}"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="Toast.hide('${toastId}')">&times;</button>
        `;

        container.appendChild(toast);

        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => this.hide(toastId), duration);
        }

        return toastId;
    },
    hide: function(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        }
    },
    success: function(message, duration = 5000) {
        return this.show(message, 'success', duration);
    },
    error: function(message, duration = 5000) {
        return this.show(message, 'danger', duration);
    },
    warning: function(message, duration = 5000) {
        return this.show(message, 'warning', duration);
    },
    info: function(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
};

// ============================================================================
// FORM VALIDATION UTILITIES
// ============================================================================

const FormValidator = {
    validateEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },
    validatePhone: function(phone) {
        const regex = /^[\d\s\-\+\(\)]+$/;
        return phone.length >= 7 && regex.test(phone);
    },
    validatePrice: function(price) {
        return !isNaN(price) && parseFloat(price) > 0;
    },
    validateRequired: function(value) {
        return value && value.trim().length > 0;
    },
    validateMinLength: function(value, minLength) {
        return value && value.length >= minLength;
    },
    validateMaxLength: function(value, maxLength) {
        return value && value.length <= maxLength;
    },
    validateFileSize: function(file, maxSizeMB) {
        const maxBytes = maxSizeMB * 1024 * 1024;
        return file && file.size <= maxBytes;
    },
    validateFileType: function(file, allowedTypes) {
        return file && allowedTypes.includes(file.type);
    },
    markFieldError: function(fieldId, errorMessage) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('is-invalid');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            }
        }
    },
    clearFieldError: function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.style.display = 'none';
            }
        }
    }
};

// ============================================================================
// FORM SUBMIT WITH LOADING STATE
// ============================================================================

function handleFormSubmit(formElement) {
    formElement.addEventListener('submit', function(e) {
        if (this.hasAttribute('data-disable-on-submit')) {
            const buttons = this.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            });
        }
    });
}

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Initialize forms with submit handlers
    const forms = document.querySelectorAll('form[data-disable-on-submit]');
    forms.forEach(form => handleFormSubmit(form));
});
