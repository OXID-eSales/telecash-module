document.addEventListener('DOMContentLoaded', function() {
    // Find select elements by their IDs
    const identSelect = document.getElementById('teleCashIdent');
    const captureTypeSelect = document.getElementById('teleCashCaptureType');

    if (!identSelect || !captureTypeSelect) return;

    // Add event listener for changes on the first select
    identSelect.addEventListener('change', async function(e) {
        const selectedIdent = e.target.value;

        try {
            // Build endpoint URL
            const endpoint = `${oTeleCashDefinitions.AdminTeleCashJsonEndpoint}${oTeleCashDefinitions.getPossibleTeleCashCaptureTypes}&${oTeleCashDefinitions.teleCashIdentKey}=${selectedIdent}`;

            // Perform API call
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const captureTypes = await response.json();

            // Store currently selected value
            const currentValue = captureTypeSelect.value;

            // Clear existing options
            captureTypeSelect.innerHTML = '';

            // Add new options based on API response
            captureTypes.forEach(captureType => {
                const option = document.createElement('option');
                option.value = captureType;

                // Get translation from definitions object
                const translationKey = `OSC_TELECASH_PAYMENT_CAPTURETYPE_${captureType}`;
                option.textContent = oTeleCashTranslations[translationKey] || captureType;

                // Mark as selected if it matches the previous value
                if (captureType === currentValue) {
                    option.selected = true;
                }

                captureTypeSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching capture types:', error);
        }
    });
});
