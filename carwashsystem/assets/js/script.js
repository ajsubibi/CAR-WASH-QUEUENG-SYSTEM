// Minimal JavaScript for Car Wash Queue System

// Auto-refresh functionality for queue display
function autoRefresh() {
    if (document.querySelector('.queue-container')) {
        setTimeout(function() {
            location.reload();
        }, 30000); // Refresh every 30 seconds
    }
}

// Form validation helpers
function validatePhone(input) {
    const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/;
    return phoneRegex.test(input.value);
}

function validateRequired(input) {
    return input.value.trim() !== '';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh for queue pages
    autoRefresh();

    // Form validation for registration
    const registrationForm = document.querySelector('.registration-form');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const requiredInputs = registrationForm.querySelectorAll('input[required], select[required]');

            let isValid = true;

            // Check required fields
            requiredInputs.forEach(function(input) {
                if (!validateRequired(input)) {
                    input.style.borderColor = '#f44336';
                    isValid = false;
                } else {
                    input.style.borderColor = '#4caf50';
                }
            });

            // Check phone format
            if (phoneInput && !validatePhone(phoneInput)) {
                phoneInput.style.borderColor = '#f44336';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please correct the highlighted fields.');
            }
        });
    }

    // Dropdown toggle for admin menu
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            menu.style.display = 'none';
        });
    });

    // Focus management for accessibility
    const focusableElements = document.querySelectorAll('button, input, select, textarea, a[href]');
    focusableElements.forEach(function(element) {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #2196F3';
        });

        element.addEventListener('blur', function() {
            this.style.outline = '';
        });
    });
});

// Utility function for confirmations
function confirmAction(message) {
    return confirm(message);
}

// Function to call next customer (staff interface)
function callNextCustomer() {
    const waitingItems = document.querySelectorAll('.queue-item:not(.in-progress)');
    if (waitingItems.length > 0) {
        const firstItem = waitingItems[0];
        const position = firstItem.querySelector('.position-badge');
        if (position) {
            alert(`Calling customer in position ${position.textContent}`);
        }
    } else {
        alert('No waiting customers in queue.');
    }
}
