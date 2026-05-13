/**
 * Modern form enhancements for School of Redemption
 * Adds real-time validation, interactive feedback, and UX improvements.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Real-time validation for required fields
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                markValid(this);
            }
        });
    });

    // Password strength indicator (if password field exists)
    const passwordInput = document.querySelector('input[type="password"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updatePasswordStrengthIndicator(strength);
        });
    }

    // Auto-format phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            formatPhoneNumber(this);
        });
    });

    // Auto-capitalize first letter of names
    const nameInputs = document.querySelectorAll('input[name*="name"], input[name*="Name"]');
    nameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = this.value.trim().replace(/\b\w/g, c => c.toUpperCase());
            }
        });
    });

    // Show character count for textareas with maxlength
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${maxLength}`;
        textarea.parentNode.appendChild(counter);

        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = `${this.value.length}/${maxLength}`;
            counter.className = `float-end ${remaining < 10 ? 'text-danger' : 'text-muted'}`;
        });
    });

    // Enhance select dropdowns with search if many options
    const largeSelects = document.querySelectorAll('select[data-enable-search]');
    largeSelects.forEach(select => {
        if (select.options.length > 10) {
            // Could integrate a library like Choices.js, but for simplicity we add a search input
            addSearchToSelect(select);
        }
    });

    // Form submission feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                // Show all validation errors
                const invalidFields = this.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    field.focus();
                    showToast('Please fill in all required fields correctly.', 'warning');
                    return;
                });
            } else {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';
                    submitBtn.disabled = true;
                }
            }
        });
    });

    // Helper functions
    function validateField(field) {
        if (field.hasAttribute('required') && field.value.trim() === '') {
            markInvalid(field, 'This field is required.');
            return false;
        }
        if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
            markInvalid(field, 'Please enter a valid email address.');
            return false;
        }
        if (field.type === 'tel' && field.value && !isValidPhone(field.value)) {
            markInvalid(field, 'Please enter a valid phone number.');
            return false;
        }
        markValid(field);
        return true;
    }

    function markInvalid(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        let feedback = field.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    function markValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function isValidPhone(phone) {
        // Simple validation for international numbers
        const re = /^[\d\s\-\+\(\)]{10,}$/;
        return re.test(phone.replace(/\s/g, ''));
    }

    function calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return score;
    }

    function updatePasswordStrengthIndicator(strength) {
        let indicator = document.getElementById('password-strength');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.className = 'mt-2';
            const passwordField = document.querySelector('input[type="password"]');
            passwordField.parentNode.appendChild(indicator);
        }
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['danger', 'danger', 'warning', 'info', 'success'];
        indicator.innerHTML = `
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-${colors[strength]}" role="progressbar" style="width: ${strength * 20}%"></div>
            </div>
            <small class="text-${colors[strength]}">${labels[strength]}</small>
        `;
    }

    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 10) value = value.substring(0, 10);
        if (value.length >= 6) {
            value = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
        } else if (value.length >= 3) {
            value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
        }
        input.value = value;
    }

    function addSearchToSelect(select) {
        const wrapper = document.createElement('div');
        wrapper.className = 'select-search-wrapper';
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control form-control-sm mb-2';
        searchInput.placeholder = 'Search options...';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(searchInput);
        wrapper.appendChild(select);

        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            Array.from(select.options).forEach(option => {
                option.style.display = option.text.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }

    function showToast(message, type = 'info') {
        // Create toast container if not exists
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});