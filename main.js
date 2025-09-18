// Main JavaScript for Legal Aid Beyond Bars platform

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#e74c3c';
            isValid = false;
        } else {
            input.style.borderColor = '#dee2e6';
        }
    });
    
    return isValid;
}

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // Add confirmation to dangerous actions
    const dangerButtons = document.querySelectorAll('.btn-danger');
    dangerButtons.forEach(button => {
        if (!button.onclick) {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to perform this action?')) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Mobile menu toggle
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('mobile-active');
}

// Case filtering
function filterCases() {
    const searchInput = document.getElementById('caseSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const table = document.querySelector('.table tbody');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const statusValue = statusFilter ? statusFilter.value : '';
    const typeValue = typeFilter ? typeFilter.value : '';
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matchesSearch = !searchTerm || text.includes(searchTerm);
        const matchesStatus = !statusValue || text.includes(statusValue.toLowerCase());
        const matchesType = !typeValue || text.includes(typeValue.toLowerCase());
        
        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add event listeners for real-time filtering
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('caseSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    if (searchInput) searchInput.addEventListener('input', filterCases);
    if (statusFilter) statusFilter.addEventListener('change', filterCases);
    if (typeFilter) typeFilter.addEventListener('change', filterCases);
});
