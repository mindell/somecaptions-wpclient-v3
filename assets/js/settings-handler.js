/**
 * Settings Handler Scripts
 * 
 * Handles the settings form submission and API validation in the SoMe Captions Client plugin
 */

(function() {
    'use strict';

    // Initialize settings handler when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initSettingsHandler();
    });

    /**
     * Initialize settings form handling
     */
    function initSettingsHandler() {
        // Handle legacy form
        const legacySettingsForm = document.getElementById('somecaptions-wpclient-settings');
        if (legacySettingsForm) {
            legacySettingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleSettingsSubmission(legacySettingsForm);
            });
        }
        
        // Handle modern form with custom button
        const modernSettingsForm = document.getElementById('somecaptions-client-settings-form');
        const saveButton = document.getElementById('somecaptions-save-settings');
        
        console.log('Settings handler initialized');
        console.log('Modern form found:', modernSettingsForm);
        console.log('Save button found:', saveButton);
        
        if (modernSettingsForm && saveButton) {
            console.log('Adding click event listener to save button');
            saveButton.addEventListener('click', function(e) {
                console.log('Save button clicked');
                e.preventDefault();
                handleSettingsSubmission(modernSettingsForm);
            });
        } else {
            console.log('Could not find modern form or save button');
        }
        
        // Add direct event listener to any button with the ID somecaptions-save-settings
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'somecaptions-save-settings') {
                console.log('Save button clicked via document event listener');
                e.preventDefault();
                
                // Try multiple ways to find the form
                let form = document.getElementById('somecaptions-client-settings-form');
                
                // If not found by ID, try to find it by class or by looking at the form closest to the button
                if (!form) {
                    console.log('Form not found by ID, trying alternative methods');
                    
                    // Try to find by class
                    form = document.querySelector('form.somecaptions-ajax-form');
                    console.log('Form by class:', form);
                    
                    // If still not found, try to find the closest form to the button
                    if (!form) {
                        form = e.target.closest('.somecaptions-form-wrapper')?.querySelector('form');
                        console.log('Form by wrapper proximity:', form);
                    }
                    
                    // Last resort: find any form in the settings container
                    if (!form) {
                        form = document.querySelector('.somecaptions-card-content form');
                        console.log('Form by card content:', form);
                    }
                    
                    // Log all forms on the page for debugging
                    console.log('All forms on page:', document.querySelectorAll('form'));
                }
                
                if (form) {
                    console.log('Found form:', form);
                    console.log('Form ID:', form.id);
                    console.log('Form action:', form.action);
                    handleSettingsSubmission(form);
                } else {
                    console.error('Form not found after all attempts');
                    
                    // Try to manually construct form data as a last resort
                    const endpoint = document.getElementById('endpoint')?.value;
                    const apiKey = document.getElementById('api_key')?.value;
                    
                    if (endpoint || apiKey) {
                        console.log('Creating manual form data from fields');
                        const manualFormData = new FormData();
                        if (endpoint) manualFormData.append('endpoint', endpoint);
                        if (apiKey) manualFormData.append('api_key', apiKey);
                        
                        // Create a temporary form element
                        const tempForm = document.createElement('form');
                        tempForm.id = 'temp-settings-form';
                        handleSettingsSubmission(tempForm, manualFormData);
                    } else {
                        alert('Could not find form or form fields. Please try again or refresh the page.');
                    }
                }
            }
        });
    }

    /**
     * Handle settings form submission
     * 
     * @param {HTMLFormElement} form - The settings form element
     * @param {FormData} manualFormData - Optional manually created form data
     */
    function handleSettingsSubmission(form, manualFormData) {
        const formContainer = form.closest('.somecaptions-form-container') || form.closest('.somecaptions-form-wrapper');
        const statusContainer = document.getElementById('api-settings-status');
        
        // Show loading state
        if (formContainer) {
            formContainer.classList.add('somecaptions-loading');
            // Show the loading overlay
            const loadingOverlay = document.querySelector('.somecaptions-loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
            }
        }
        
        // Show saving message
        showNotification('info', somecaptions_admin.i18n.savingSettings || 'Saving settings...');
        
        // Get the form values
        let endpoint, apiKey;
        
        // If we have manual form data, use it
        if (manualFormData) {
            endpoint = manualFormData.get('endpoint');
            apiKey = manualFormData.get('api_key');
        } else {
            // Extract the fields from the form
            endpoint = form.querySelector('#endpoint')?.value;
            apiKey = form.querySelector('#api_key')?.value;
        }
        
        // Create FormData and append the values
        const formData = new FormData();
        formData.append('action', 'somecaptions_save_settings');
        
        // Add the field values using the correct format for the option key used in sw_get_settings()
        // The option key is 'somecaptions-client-settings' as seen in the sw_get_settings() function
        formData.append('somecaptions-client-settings[endpoint]', endpoint);
        formData.append('somecaptions-client-settings[api_key]', apiKey);
        
        // Also send as direct fields for our custom handler
        formData.append('endpoint', endpoint);
        formData.append('api_key', apiKey);
        
        // Add nonce for security
        if (somecaptions_admin && somecaptions_admin.nonce) {
            formData.append('_wpnonce', somecaptions_admin.nonce);
            console.log('Using admin nonce:', somecaptions_admin.nonce);
        } else {
            // Try to find nonce in the form
            const nonceField = form.querySelector('input[name="_wpnonce"]');
            if (nonceField) {
                formData.append('_wpnonce', nonceField.value);
                console.log('Found nonce in form:', nonceField.value);
            } else {
                console.warn('No nonce found for AJAX request');
            }
        }
        
        // Convert FormData to URLSearchParams for fetch
        const searchParams = new URLSearchParams();
        for (const pair of formData) {
            searchParams.append(pair[0], pair[1]);
        }
        
        // Log the final request parameters
        console.log('Final request parameters:', searchParams.toString());
        
        // Save the settings via our custom AJAX handler
        fetch(somecaptions_admin.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: searchParams
        })
        .then(response => response.json())
        .then(saveResponse => {
            console.log('Settings saved successfully', saveResponse);
            
            if (saveResponse.success) {
                showNotification('success', saveResponse.data?.message || somecaptions_admin.i18n.settingsSaved || 'Settings saved successfully!');
                
                // Reload the page after a short delay to show the success message
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                return;
            }
            
            // If there was an error in saving, show it
            if (!saveResponse.success) {
                showNotification('error', saveResponse.data?.message || 'Error saving settings');
                return;
            }
            
            // After saving, validate the API connection
            // Use the values we already have
            if (!endpoint || !apiKey) {
                console.error('Missing endpoint or API key for validation');
                return;
            }
            
            showNotification('info', somecaptions_admin.i18n.validatingApi);
            
            // Make the AJAX request to validate API
            const validationData = {
                action: 'somecaptions_validate_api',
                nonce: somecaptions_admin.nonce,
                endpoint: endpoint,
                api_key: apiKey
            };
            
            console.log('Validating API with:', endpoint, 'and key:', apiKey);
            
            return fetch(somecaptions_admin.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(validationData)
            });
        })
        .then(response => response.json())
        .then(response => {
            // Hide loading state
            if (formContainer) {
                formContainer.classList.remove('somecaptions-loading');
                
                // Hide the loading overlay
                const loadingOverlay = document.querySelector('.somecaptions-loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                }
            }
            
            if (response.success) {
                // Show success message
                showNotification('success', somecaptions_admin.i18n.apiValidated || 'API key validated successfully!');
                
                // Hide the loading overlay
                document.getElementById('somecaptions-api-validation-overlay').style.display = 'none';
                
                // Reload the page to show the updated UI with proper connection status
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification('error', response.data.message || somecaptions_admin.i18n.connectionFailed);
            }
        })
        .catch(error => {
            // Hide loading state
            if (formContainer) {
                formContainer.classList.remove('somecaptions-loading');
                
                // Hide the loading overlay
                const loadingOverlay = document.querySelector('.somecaptions-loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                }
            }
            
            console.error('Settings submission error:', error);
            showNotification('error', somecaptions_admin.i18n.error);
        });
    }

    /**
     * Show notification message
     * 
     * @param {string} type - Type of notification (success, error, info)
     * @param {string} message - Message to display
     */
    function showNotification(type, message) {
        const container = document.getElementById('api-settings-status');
        
        if (!container) return;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `somecaptions-notification somecaptions-notification-${type}`;
        
        // Add icon based on type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '✓';
                break;
            case 'error':
                icon = '✗';
                break;
            case 'info':
                icon = 'ℹ';
                break;
        }
        
        notification.innerHTML = `
            <span class="somecaptions-notification-icon">${icon}</span>
            <span class="somecaptions-notification-message">${message}</span>
        `;
        
        // Clear previous notifications
        container.innerHTML = '';
        container.appendChild(notification);
        
        // Make sure the container is visible
        container.style.display = 'block';
        
        // Auto-dismiss success notifications after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                notification.classList.add('somecaptions-notification-fade');
                setTimeout(() => {
                    if (container.contains(notification)) {
                        container.removeChild(notification);
                    }
                }, 500);
            }, 5000);
        }
    }

    // The updateDomainTab function has been removed as we're now using static tabs
    // This improves tab navigation reliability by ensuring all tabs are present in the DOM from the start
})();
