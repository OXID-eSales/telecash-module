/**
 * Payment Form Handler for Credit Card and SEPA Direct Debit
 *
 * This script provides client-side validation and formatting for payment forms
 * in an OXID eShop environment. It handles both credit card and SEPA direct debit
 * payment methods with real-time validation and formatting.
 *
 * Features:
 * - Credit Card validation using Luhn algorithm
 * - IBAN validation with country-specific rules
 * - Real-time input formatting
 * - Expiry date validation
 * - Form submission handling
 * - Smooth scroll to validation errors
 * - Bootstrap validation integration
 *
 * Requirements:
 * - OXID eShop environment with Bootstrap 4.x
 * - DOM Elements:
 *   Credit Card Form:
 *   - Form with data-payment-type="credit-card"
 *   - #ccNumber for card number input
 *   - #ccExpiry for expiry date (MM/YY format)
 *   - #ccCVC for security code
 *   - #ccName for cardholder name
 *
 *   SEPA Form:
 *   - Form with data-payment-type="sepa"
 *   - #sepaIBAN for IBAN input
 *   - #sepaHolder for account holder name
 *
 *   Order Forms:
 *   - #orderConfirmAgbTop for hidden fields
 *   - #orderConfirmAgbBottom for form submission
 *
 * Browser Support:
 * - Modern browsers with ES6+ support
 * - Required features:
 *   - classList API
 *   - querySelector/querySelectorAll
 *   - Array.prototype.map/every/find
 *   - Template literals
 *   - Arrow functions
 *   - const/let declarations
 *
 * Input Validation:
 * Credit Card:
 * - Card number: 13-19 digits, passes Luhn check
 * - Expiry date: Valid future date in MM/YY format
 * - CVC: 3-4 digits
 * - Name: Required field
 *
 * SEPA:
 * - IBAN: Country-specific length, checksum validation
 * - Account holder: Required field
 *
 * Form Handling:
 * On successful validation, the script:
 * 1. Collects form data
 * 2. Creates hidden input fields
 * 3. Injects fields into both order forms
 * 4. Submits the bottom form
 */
(function() {
    'use strict';

    /**
     * Implements the Luhn algorithm for credit card validation
     * @param {string} cardNumber - The card number to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    function isValidLuhn(cardNumber) {

        // Remove spaces and non-digit characters
        const digits = cardNumber.replace(/\D/g, '');

        if (digits.length < 13 || digits.length > 19) {
            return false;
        }

        let sum = 0;
        let double = false;

        // Loop from right to left
        for (let i = digits.length - 1; i >= 0; i--) {
            let digit = parseInt(digits[i], 10);

            // Double every second digit, starting from the right
            if (double) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }

            sum += digit;
            double = !double;
        }

        // The number is valid if the sum is divisible by 10
        return sum % 10 === 0;
    }

    /**
     * Validates an IBAN
     * @param {string} iban - The IBAN to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    function isValidIBAN(iban) {
        // Remove spaces and convert to uppercase
        const ibanClean = iban.replace(/\s+/g, '').toUpperCase();

        // Basic format check
        if (!/^[A-Z]{2}(\d{2})[A-Z0-9]+$/.test(ibanClean)) {
            return false;
        }

        // Length check based on country code
        const countryLengths = {
            'AT': 20, 'BE': 16, 'BG': 22, 'CH': 21, 'CY': 28, 'CZ': 24,
            'DE': 22, 'DK': 18, 'EE': 20, 'ES': 24, 'FI': 18, 'FR': 27,
            'GB': 22, 'GI': 23, 'GR': 27, 'HR': 21, 'HU': 28, 'IE': 22,
            'IS': 26, 'IT': 27, 'LI': 21, 'LT': 20, 'LU': 20, 'LV': 21,
            'MC': 27, 'MT': 31, 'NL': 18, 'NO': 15, 'PL': 28, 'PT': 25,
            'RO': 24, 'SE': 24, 'SI': 19, 'SK': 24
        };

        const countryCode = ibanClean.substr(0, 2);
        if (countryLengths[countryCode] && ibanClean.length !== countryLengths[countryCode]) {
            return false;
        }

        // Convert IBAN to integer technical form
        let rearranged = ibanClean.substr(4) + ibanClean.substr(0, 4);
        let numerical = '';

        for (let i = 0; i < rearranged.length; i++) {
            numerical += rearranged.charCodeAt(i) >= 65
                ? (rearranged.charCodeAt(i) - 55).toString()
                : rearranged[i];
        }

        // Calculate mod-97
        let remainder = numerical;
        let block;

        while (remainder.length > 2) {
            block = remainder.slice(0, 9);
            remainder = (parseInt(block, 10) % 97).toString() + remainder.slice(9);
        }

        return parseInt(remainder, 10) % 97 === 1;
    }

    /**
     * Validates expiry date
     * @param {string} expiry - The expiry date in MM/YY format
     * @returns {boolean} - True if valid and not expired, false otherwise
     */
    function isValidExpiry(expiry) {
        // Check basic format (MM/YY)
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            return false;
        }

        const [month, year] = expiry.split('/').map(num => parseInt(num, 10));
        const now = new Date();
        const expireDate = new Date(2000 + year, month - 1); // month - 1 because months are 0-based

        // Check if month is valid
        if (month < 1 || month > 12) {
            return false;
        }

        // Set both dates to the first of the month for proper comparison
        expireDate.setDate(1);
        now.setDate(1);
        now.setHours(0, 0, 0, 0);

        return expireDate >= now;
    }

    /**
     * Formats credit card number by adding spaces after every 4 digits
     */
    function formatCreditCardNumber(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
    }

    /**
     * Formats expiry date by adding slash after month
     */
    function formatExpiryDate(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2);
        }
        e.target.value = value;
    }

    /**
     * Formats IBAN by adding spaces and converting to uppercase
     */
    function formatIBAN(e) {
        let value = e.target.value.replace(/\s+/g, '').toUpperCase();
        e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
    }

    /**
     * Validates a single form field
     */
    function validateField(field) {
        field.classList.remove('is-valid', 'is-invalid');
        let isValid = field.checkValidity();

        // Additional validation for credit card fields
        if (field.id === 'ccNumber' && isValid) {
            const cardNumber = field.value.replace(/\s+/g, '');
            isValid = isValidLuhn(cardNumber);
        }

        // Additional validation for expiry date
        if (field.id === 'ccExpiry' && isValid) {
            isValid = isValidExpiry(field.value);
        }

        // Additional validation for IBAN
        if (field.id === 'sepaIBAN' && isValid) {
            isValid = isValidIBAN(field.value);
        }

        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        return isValid;
    }

    /**
     * Creates a hidden input field
     */
    function createHiddenField(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    /**
     * Transfers payment form data to the order confirmation form
     */
    function transferPaymentDataToOrderForm(paymentData) {
        // Transfer to both forms to ensure data is present in the submitted one
        const orderForms = [
            document.getElementById('orderConfirmAgbTop'),
            document.getElementById('orderConfirmAgbBottom')
        ];

        orderForms.forEach(form => {
            if (!form) {
                console.error('Order form not found');
                return;
            }

            // Remove any existing payment fields
            const existingFields = form.querySelectorAll('input[name^="payment"]');
            existingFields.forEach(field => field.remove());

            // Add new payment fields
            Object.entries(paymentData).forEach(([key, value]) => {
                form.appendChild(createHiddenField(key, value));
            });
        });
    }

    /**
     * Scrolls smoothly to the first invalid field
     * @param {Object} fields - Object containing form fields
     */
    function scrollToFirstError(fields) {
        const firstInvalidField = Object.values(fields).find(field => !field.checkValidity());
        if (firstInvalidField) {
            firstInvalidField.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    /**
     * Validates the credit card form and collects its data
     */
    function validateCreditCardForm() {
        const form = document.querySelector('form[data-payment-type="credit-card"]');
        if (!form) return null;

        const fields = {
            ccNumber: document.getElementById('ccNumber'),
            ccExpiry: document.getElementById('ccExpiry'),
            ccCVC: document.getElementById('ccCVC'),
            ccName: document.getElementById('ccName')
        };

        // Check if all fields exist
        if (!Object.values(fields).every(field => field)) {
            return false;
        }

        // Validate all fields and collect the results
        const validationResults = Object.values(fields).map(field => validateField(field));

        // If validation failed, scroll to first error
        if (validationResults.includes(false)) {
            scrollToFirstError(fields);
            return false;
        }

        return {
            'payment[cc_number]': fields.ccNumber.value.replace(/\s+/g, ''),
            'payment[cc_expiry]': fields.ccExpiry.value,
            'payment[cc_cvc]': fields.ccCVC.value,
            'payment[cc_name]': fields.ccName.value
        };
    }

    /**
     * Validates the SEPA form and collects its data
     */
    function validateSEPAForm() {
        const form = document.querySelector('form[data-payment-type="sepa"]');
        if (!form) return null;

        const fields = {
            sepaIBAN: document.getElementById('sepaIBAN'),
            sepaHolder: document.getElementById('sepaHolder')
        };

        // Check if all fields exist
        if (!Object.values(fields).every(field => field)) {
            return false;
        }

        // Validate all fields and collect the results
        const validationResults = Object.values(fields).map(field => validateField(field));

        // If validation failed, scroll to first error
        if (validationResults.includes(false)) {
            scrollToFirstError(fields);
            return false;
        }

        return {
            'payment[sepa_iban]': fields.sepaIBAN.value.replace(/\s+/g, ''),
            'payment[sepa_holder]': fields.sepaHolder.value
        };
    }

    /**
     * Handles the order submission process
     */
    function handleOrderSubmit(event) {
        event.preventDefault();

        // Check for credit card form
        const ccData = validateCreditCardForm();
        if (ccData === false) {
            return false;
        }

        // Check for SEPA form
        const sepaData = validateSEPAForm();
        if (sepaData === false) {
            return false;
        }

        // Transfer the appropriate data
        if (ccData) {
            transferPaymentDataToOrderForm(ccData);
        } else if (sepaData) {
            transferPaymentDataToOrderForm(sepaData);
        }

        // Submit the main form
        const mainForm = document.getElementById('orderConfirmAgbBottom');
        if (mainForm) {
            mainForm.submit();
        }
    }

    /**
     * Adds validation listeners to form fields
     */
    function addValidationListeners(formType) {
        const fields = formType === 'credit-card'
            ? ['ccNumber', 'ccExpiry', 'ccCVC', 'ccName']
            : ['sepaIBAN', 'sepaHolder'];

        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', () => {
                    validateField(field);
                });
                field.addEventListener('input', () => {
                    field.classList.remove('is-valid', 'is-invalid');
                });
            }
        });
    }

    /**
     * Initialize all event listeners when DOM is ready
     */
    function initialize() {
        // Credit card form listeners
        const ccNumberInput = document.getElementById('ccNumber');
        if (ccNumberInput) {
            ccNumberInput.addEventListener('input', formatCreditCardNumber);
            addValidationListeners('credit-card');
        }

        const ccExpiryInput = document.getElementById('ccExpiry');
        if (ccExpiryInput) {
            ccExpiryInput.addEventListener('input', formatExpiryDate);
        }

        // SEPA form listeners
        const sepaIBANInput = document.getElementById('sepaIBAN');
        if (sepaIBANInput) {
            sepaIBANInput.addEventListener('input', formatIBAN);
            addValidationListeners('sepa');
        }

        // Override the submit button click
        const submitButton = document.querySelector('button[onclick*="orderConfirmAgbBottom"]');
        if (submitButton) {
            submitButton.onclick = null; // Remove the inline onclick
            submitButton.addEventListener('click', handleOrderSubmit);
        }
    }

    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();