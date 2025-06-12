/**
 * Domain Verification Scripts
 * 
 * Handles the domain verification process in the SoMe Captions Client plugin
 */

(function() {
    'use strict';

    // Initialize domain verification when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initDomainVerification();
    });

    /**
     * Initialize domain verification functionality
     */
    function initDomainVerification() {
        const verifyButton = document.getElementById('verify-domain-btn');
        
        if (!verifyButton) return;
        
        verifyButton.addEventListener('click', function() {
            handleDomainVerification();
        });
        
        // Also listen for the custom event that might be triggered when the tab is loaded dynamically
        document.addEventListener('somecaptions_domain_tab_loaded', function() {
            initDomainVerification();
        });
    }

    /**
     * Handle domain verification process
     */
    function handleDomainVerification() {
        const verificationCode = document.querySelector('#verification_code');
        const verificationStatus = document.getElementById('verification-status');
        const formContainer = verificationCode.closest('.somecaptions-form-container');
        
        if (!verificationCode || !verificationCode.value) {
            showVerificationMessage('error', somecaptionsDomainVerification.pleaseEnterCode || 'Please enter a verification code');
            return;
        }
        
        // Show loading state
        if (formContainer) {
            formContainer.classList.add('somecaptions-loading');
        }
        
        showVerificationMessage('info', somecaptionsDomainVerification.verifying);
        
        // Make the AJAX request to verify domain
        const data = {
            action: 'somecaptions_verify_domain',
            verification_code: verificationCode.value,
            nonce: somecaptionsDomainVerification.nonce
        };
        
        fetch(somecaptionsDomainVerification.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(response => {
            // Hide loading state
            if (formContainer) {
                formContainer.classList.remove('somecaptions-loading');
            }
            
            if (response.success) {
                showVerificationMessage('success', response.data.message || somecaptionsDomainVerification.success);
                
                // Reload the page after a short delay to show the updated UI
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showVerificationMessage('error', response.data.message || somecaptionsDomainVerification.error);
            }
        })
        .catch(error => {
            // Hide loading state
            if (formContainer) {
                formContainer.classList.remove('somecaptions-loading');
            }
            
            console.error('Domain verification error:', error);
            showVerificationMessage('error', somecaptionsDomainVerification.error);
        });
    }

    /**
     * Show verification status message
     * 
     * @param {string} type - Message type (success, error, info)
     * @param {string} message - Message to display
     */
    function showVerificationMessage(type, message) {
        const statusElement = document.getElementById('verification-status');
        
        if (!statusElement) return;
        
        // Remove existing status classes
        statusElement.classList.remove(
            'somecaptions-status-success',
            'somecaptions-status-error',
            'somecaptions-status-info'
        );
        
        // Add appropriate class based on type
        statusElement.classList.add(`somecaptions-status-${type}`);
        
        // Set the message
        statusElement.textContent = message;
        statusElement.setAttribute('aria-live', 'polite');
        
        // Make sure the status element is visible
        statusElement.style.display = 'block';
    }
})();
