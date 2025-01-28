/**
 * TeleCash Form Handler
 *
 * Handles the form submission process for TeleCash payment integration in OXID 7.
 * Validates required checkboxes before submitting the TeleCash form.
 *
 * Browser Support:
 * - Chrome: 51+
 * - Firefox: 50+
 * - Safari: 10+
 * - Edge: 15+
 * - Opera: 38+
 *
 * Note: The code uses ES6 features like classes, arrow functions, and const/let declarations.
 * For older browsers, consider using a transpiler like Babel.
 */

class TeleCashFormHandler {
    /**
     * Initialize the form handler and set up event listeners
     */
    constructor() {
        this.setupEventListeners();
    }

    /**
     * Set up event listeners for the submit button
     * Removes inline onclick handler and replaces it with our custom handler
     */
    setupEventListeners() {
        const submitButton = document.getElementById('checkoutOrderNextStepSide');
        const teleCashForm = document.getElementById('teleCashConnectForm');

        if (submitButton && teleCashForm) {
            submitButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSubmit(teleCashForm);
            });
        }
    }

    /**
     * Validate a single checkbox by ID
     *
     * @param {string} id - The ID of the checkbox to validate
     * @returns {boolean} True if checkbox is checked or doesn't exist, false otherwise
     */
    validateCheckbox(id) {
        const checkbox = document.getElementById(id);
        if (!checkbox) {
            // If checkbox doesn't exist, consider it as valid
            return true;
        }
        return checkbox.checked;
    }

    /**
     * Focus the first invalid (unchecked) checkbox in the form
     *
     * @returns {boolean} True if an invalid checkbox was found and focused, false otherwise
     */
    focusFirstInvalidCheckbox() {
        const checkboxIds = [
            'checkAgbTop',
            'oxdownloadableproductsagreement',
            'oxserviceproductsagreement'
        ];

        for (const id of checkboxIds) {
            const checkbox = document.getElementById(id);
            if (checkbox && !checkbox.checked) {
                checkbox.focus();
                checkbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return true;
            }
        }
        return false;
    }

    /**
     * Validate all required checkboxes
     *
     * Checks if all required checkboxes are either checked or not present
     *
     * @returns {boolean} True if all checkboxes are valid, false otherwise
     */
    validateAllCheckboxes() {
        return (
            this.validateCheckbox('checkAgbTop') &&
            this.validateCheckbox('oxdownloadableproductsagreement') &&
            this.validateCheckbox('oxserviceproductsagreement')
        );
    }

    /**
     * Handle the submit action
     *
     * Validates all checkboxes and submits the TeleCash form if validation passes
     *
     * @param {HTMLFormElement} teleCashForm - The TeleCash form element to be submitted
     * @returns {void}
     */
    handleSubmit(teleCashForm) {
        if (!this.validateAllCheckboxes()) {
            this.focusFirstInvalidCheckbox();
            return;
        }

        if (teleCashForm) {
            // Disable all buttons
            document.querySelectorAll('button').forEach(button => {
                button.disabled = true;
            });

            // Submit the TeleCash form
            teleCashForm.submit();
        } else {
            console.error('TeleCash form not found');
        }
    }
}

// Initialize the handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new TeleCashFormHandler();
});