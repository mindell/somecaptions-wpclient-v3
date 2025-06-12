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
        const settingsForm = document.getElementById('somecaptions-wpclient-settings');
        
        if (!settingsForm) return;
        
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSettingsSubmission(settingsForm);
        });
    }

    /**
     * Handle settings form submission
     * 
     * @param {HTMLFormElement} form - The settings form element
     */
    function handleSettingsSubmission(form) {
        const formContainer = form.closest('.somecaptions-form-container');
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
        showNotification('info', somecaptions_admin.i18n.savingSettings);
        
        // Get form data
        const formData = new FormData(form);
        formData.append('action', 'cmb2_save_options-page_fields');
        formData.append('object_id', 'somecaptions-wpclient_options');
        
        // Convert FormData to URLSearchParams for fetch
        const searchParams = new URLSearchParams();
        for (const pair of formData) {
            searchParams.append(pair[0], pair[1]);
        }
        
        // First save the form via AJAX
        fetch(somecaptions_admin.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: searchParams
        })
        .then(response => response.json())
        .then(saveResponse => {
            console.log('Settings saved successfully', saveResponse);
            
            // After saving, validate the API connection
            const endpoint = document.getElementById('endpoint').value;
            const apiKey = document.getElementById('api_key').value;
            
            showNotification('info', somecaptions_admin.i18n.validatingApi);
            
            // Make the AJAX request to validate API
            const validationData = {
                action: 'somecaptions_validate_api',
                endpoint: endpoint,
                api_key: apiKey,
                nonce: somecaptions_admin.nonce
            };
            
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
