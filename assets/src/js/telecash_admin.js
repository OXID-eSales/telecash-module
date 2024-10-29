/**
 * TeleCash Admin Interface JavaScript
 *
 * This script handles the dynamic updating of capture type select options
 * based on the selected TeleCash payment identifier.
 *
 * Requirements:
 * - ECMAScript 2017+ for async/await support
 * - ECMAScript 2015+ (ES6) for:
 *   - Template literals (`string ${variable}`)
 *   - Arrow functions (() => {})
 *   - Object methods (Object.entries())
 *   - const/let declarations
 *
 * Browser Support:
 * - Chrome 55+
 * - Firefox 52+
 * - Safari 11+
 * - Edge 15+
 *
 * Required global objects:
 * @requires oTeleCashDefinitions - Object containing endpoint URLs and keys
 * @requires oTeleCashTranslations - Object containing translation strings
 */

document.addEventListener('DOMContentLoaded', function() {
    // Retrieve the required select elements from the DOM
    // These selects form a dependent pair where the second's options depend on the first's selection
    const identSelect = document.getElementById('teleCashIdent');
    const captureTypeSelect = document.getElementById('teleCashCaptureType');

    // Early exit if required elements are not found
    // This prevents errors in case the script runs on wrong pages
    if (!identSelect || !captureTypeSelect) return;

    /**
     * Event handler for changes on the ident select
     * Uses async/await for clean handling of the API request
     *
     * The handler:
     * 1. Retrieves selected value
     * 2. Fetches corresponding capture types from API
     * 3. Updates the capture type select with new options
     * 4. Maintains the currently selected value if still available
     *
     * @param {Event} e - The change event object
     */
    identSelect.addEventListener('change', async function(e) {
        const selectedIdent = e.target.value;

        try {
            // Construct the API endpoint URL using template literals
            // This modern string interpolation provides cleaner string construction
            // compared to traditional concatenation
            const endpoint = `${oTeleCashDefinitions.AdminTeleCashJsonEndpoint}${oTeleCashDefinitions.getPossibleTeleCashCaptureTypes}&${oTeleCashDefinitions.teleCashIdentKey}=${selectedIdent}`;

            // Perform API request using the Fetch API
            // The Accept header ensures we receive JSON data
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            // Check for HTTP errors
            // Template literals are used for the error message to include the status code
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Parse the JSON response
            // Expected format: { "0": "direct", "1": "ondelivery", "2": "manually" }
            const captureTypes = await response.json();

            // Store the currently selected value to maintain it if still available
            const currentValue = captureTypeSelect.value;

            // Clear existing options before adding new ones
            captureTypeSelect.innerHTML = '';

            // Use Object.entries to iterate over the response object
            // This maintains the object structure while allowing easy access to values
            // The index (_) is ignored as we only need the captureType
            Object.entries(captureTypes).forEach(([_, captureType]) => {
                // Create and configure the new option element
                const option = document.createElement('option');
                option.value = captureType;

                // Construct the translation key and get the translated text
                // Falls back to the capture type itself if no translation is found
                const translationKey = `OSC_TELECASH_PAYMENT_CAPTURETYPE_${captureType}`;
                option.textContent = oTeleCashTranslations[translationKey] || captureType;

                // Preserve the previous selection if the option is still available
                if (captureType === currentValue) {
                    option.selected = true;
                }

                // Add the new option to the select element
                captureTypeSelect.appendChild(option);
            });
        } catch (error) {
            // Log any errors to the console for debugging
            // In production, you might want to show a user-friendly error message
            console.error('Error fetching capture types:', error);
        }
    });
});