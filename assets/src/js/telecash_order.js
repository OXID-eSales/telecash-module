/**
 * Credit Card Form Validation and Formatting
 *
 * This script provides client-side functionality for credit card form fields:
 * - Automatic formatting of credit card numbers (adding spaces)
 * - Automatic formatting of expiry date (adding slash)
 * - Bootstrap 4 form validation integration
 *
 * Requirements:
 * - ECMAScript 6+ (ES2015+)
 * - Bootstrap 4.x for validation classes
 * - DOM with specific input fields:
 *   - #ccNumber for credit card number
 *   - #ccExpiry for expiry date
 *   - Form with class .needs-validation
 *
 * Browser Support:
 * - Chrome 51+
 * - Firefox 54+
 * - Safari 10+
 * - Edge 15+
 *
 * Note: For older browsers, consider adding polyfills for:
 * - Array.prototype.filter
 * - addEventListener
 * - querySelector/querySelectorAll
 */

(function() {
    'use strict';

    /**
     * Formats credit card number by adding spaces after every 4 digits
     *
     * @param {Event} e Input event object
     * Requirements:
     * - Input element must have 'value' property
     * - Input element must allow setting 'value'
     */
    function formatCreditCardNumber(e) {
        // Remove existing spaces and non-numeric characters
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');

        // Add space after every 4 characters and trim
        let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();

        // Update input value
        e.target.value = formattedValue;
    }

    /**
     * Formats expiry date by adding slash after month
     *
     * @param {Event} e Input event object
     * Requirements:
     * - Input element must have 'value' property
     * - Input element must allow setting 'value'
     */
    function formatExpiryDate(e) {
        // Remove non-numeric characters
        let value = e.target.value.replace(/\D/g, '');

        // Add slash after month if length is sufficient
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2);
        }

        // Update input value
        e.target.value = value;
    }

    /**
     * Initializes Bootstrap validation
     *
     * Sets up event listeners for form validation using Bootstrap 4
     * Requirements:
     * - Bootstrap 4.x CSS classes
     * - Form with class 'needs-validation'
     */
    function initializeValidation() {
        // Get all forms with the 'needs-validation' class
        var forms = document.getElementsByClassName('needs-validation');

        // Add submit event handler to each form
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    /**
     * Initialize all event listeners when DOM is ready
     */
    function initialize() {
        // Credit card number formatting
        const ccNumberInput = document.getElementById('ccNumber');
        if (ccNumberInput) {
            ccNumberInput.addEventListener('input', formatCreditCardNumber);
        }

        // Expiry date formatting
        const ccExpiryInput = document.getElementById('ccExpiry');
        if (ccExpiryInput) {
            ccExpiryInput.addEventListener('input', formatExpiryDate);
        }

        // Bootstrap validation
        initializeValidation();
    }

    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();