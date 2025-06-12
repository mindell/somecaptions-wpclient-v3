/**
 * SoMe Captions Client Admin Scripts
 * 
 * Main JavaScript file for the SoMe Captions Client admin interface
 */

const SomeCaptionsAdmin = (function() {
    'use strict';
    
    // Settings object to store configuration
    let settings = {
        ajaxUrl: '',
        nonce: '',
        i18n: {
            saving: 'Saving...',
            saved: 'Settings saved successfully',
            error: 'An error occurred',
            verifying: 'Verifying...',
            verified: 'Domain verified successfully',
            verificationFailed: 'Domain verification failed'
        }
    };
    
    /**
     * Initialize the admin interface
     * @param {Object} options - Configuration options
     */
    function init(options) {
        // Merge default settings with options
        settings = Object.assign(settings, options);
        
        // Initialize modules
        TabNavigation.init();
        FormHandler.init();
        NotificationSystem.init();
        DomainVerification.init();
        
        // Add event listeners for general UI interactions
        setupEventListeners();
    }
    
    /**
     * Set up general event listeners
     */
    function setupEventListeners() {
        // Toggle collapsible sections
        document.querySelectorAll('.somecaptions-collapsible-trigger').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('data-target'));
                if (target) {
                    target.classList.toggle('somecaptions-hidden');
                    this.classList.toggle('somecaptions-expanded');
                }
            });
        });
        
        // Initialize tooltips
        document.querySelectorAll('.somecaptions-has-tooltip').forEach(element => {
            const tooltipText = element.getAttribute('data-tooltip');
            if (tooltipText) {
                const tooltip = document.createElement('span');
                tooltip.className = 'somecaptions-tooltip-text';
                tooltip.textContent = tooltipText;
                element.appendChild(tooltip);
            }
        });
    }
    
    // Tab Navigation Module
    const TabNavigation = (function() {
        function init() {
            const tabs = document.querySelectorAll('.somecaptions-nav-tab');
            
            if (tabs.length === 0) return;
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the target panel
                    const targetId = this.getAttribute('href');
                    const targetPanel = document.querySelector(targetId);
                    
                    if (!targetPanel) return;
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('somecaptions-nav-tab-active'));
                    this.classList.add('somecaptions-nav-tab-active');
                    
                    // Show the selected panel
                    document.querySelectorAll('.somecaptions-tab-panel').forEach(panel => {
                        panel.classList.add('somecaptions-hidden');
                    });
                    targetPanel.classList.remove('somecaptions-hidden');
                    
                    // Save active tab in session storage
                    sessionStorage.setItem('somecaptions_active_tab', targetId);
                });
            });
            
            // Restore active tab from session storage if available
            const activeTab = sessionStorage.getItem('somecaptions_active_tab');
            if (activeTab && document.querySelector(activeTab)) {
                document.querySelector(`[href="${activeTab}"]`).click();
            } else if (tabs.length > 0) {
                // Default to first tab
                tabs[0].click();
            }
        }
        
        return {
            init: init
        };
    })();
    
    // Form Handler Module
    const FormHandler = (function() {
        function init() {
            const forms = document.querySelectorAll('.somecaptions-settings-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', handleFormSubmit);
            });
        }
        
        function handleFormSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('[type="submit"]');
            const originalButtonText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = settings.i18n.saving;
            form.classList.add('somecaptions-loading');
            
            // Add action and nonce
            formData.append('action', form.getAttribute('data-action') || 'somecaptions_save_settings');
            formData.append('nonce', settings.nonce);
            
            // Send AJAX request
            fetch(settings.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                // Reset form state
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                form.classList.remove('somecaptions-loading');
                
                if (data.success) {
                    NotificationSystem.showNotification('success', data.data.message || settings.i18n.saved);
                    
                    // Trigger custom event for successful form submission
                    const event = new CustomEvent('somecaptions:formSaved', { 
                        detail: { 
                            formId: form.id,
                            response: data 
                        } 
                    });
                    document.dispatchEvent(event);
                } else {
                    NotificationSystem.showNotification('error', data.data.message || settings.i18n.error);
                }
            })
            .catch(error => {
                // Reset form state
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                form.classList.remove('somecaptions-loading');
                
                NotificationSystem.showNotification('error', settings.i18n.error);
                console.error('Form submission error:', error);
            });
        }
        
        return {
            init: init
        };
    })();
    
    // Notification System Module
    const NotificationSystem = (function() {
        let container;
        
        function init() {
            // Create notification container if it doesn't exist
            if (!document.querySelector('.somecaptions-notifications')) {
                container = document.createElement('div');
                container.className = 'somecaptions-notifications';
                document.querySelector('.somecaptions-admin-wrap').appendChild(container);
            } else {
                container = document.querySelector('.somecaptions-notifications');
            }
        }
        
        function showNotification(type, message, duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `somecaptions-notification somecaptions-notification-${type}`;
            notification.innerHTML = `
                <div class="somecaptions-notification-content">
                    <span class="somecaptions-notification-icon"></span>
                    <span class="somecaptions-notification-message">${message}</span>
                </div>
                <button type="button" class="somecaptions-notification-close">&times;</button>
            `;
            
            // Add to container
            container.appendChild(notification);
            
            // Add close button functionality
            notification.querySelector('.somecaptions-notification-close').addEventListener('click', function() {
                notification.classList.add('somecaptions-notification-hiding');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            });
            
            // Auto-hide after duration
            setTimeout(() => {
                if (notification.parentNode === container) {
                    notification.classList.add('somecaptions-notification-hiding');
                    setTimeout(() => {
                        if (notification.parentNode === container) {
                            container.removeChild(notification);
                        }
                    }, 300);
                }
            }, duration);
            
            return notification;
        }
        
        return {
            init: init,
            showNotification: showNotification
        };
    })();
    
    // Domain Verification Module
    const DomainVerification = (function() {
        function init() {
            const verifyButton = document.getElementById('verify-domain-button');
            if (verifyButton) {
                verifyButton.addEventListener('click', handleVerification);
            }
        }
        
        function handleVerification() {
            const verificationCode = document.getElementById('verification_code').value;
            const statusElement = document.getElementById('verification-status');
            
            if (!verificationCode) {
                statusElement.innerHTML = `<span class="somecaptions-status somecaptions-status-error">
                    <span class="somecaptions-status-icon">✗</span>
                    Please enter a verification code
                </span>`;
                return;
            }
            
            // Show loading state
            statusElement.innerHTML = `<span class="somecaptions-status somecaptions-status-info">
                <span class="somecaptions-status-icon">⟳</span>
                ${settings.i18n.verifying}
            </span>`;
            
            const formData = new FormData();
            formData.append('action', 'somecaptions_verify_domain');
            formData.append('verification_code', verificationCode);
            formData.append('nonce', settings.nonce);
            
            // Send AJAX request
            fetch(settings.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusElement.innerHTML = `<span class="somecaptions-status somecaptions-status-success">
                        <span class="somecaptions-status-icon">✓</span>
                        ${data.data.message || settings.i18n.verified}
                    </span>`;
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    statusElement.innerHTML = `<span class="somecaptions-status somecaptions-status-error">
                        <span class="somecaptions-status-icon">✗</span>
                        ${data.data.message || settings.i18n.verificationFailed}
                    </span>`;
                }
            })
            .catch(error => {
                statusElement.innerHTML = `<span class="somecaptions-status somecaptions-status-error">
                    <span class="somecaptions-status-icon">✗</span>
                    ${settings.i18n.error}
                </span>`;
                console.error('Verification error:', error);
            });
        }
        
        return {
            init: init,
            handleVerification: handleVerification
        };
    })();
    
    // Public API
    return {
        init: init,
        showNotification: NotificationSystem.showNotification
    };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with WordPress localized data
    SomeCaptionsAdmin.init({
        ajaxUrl: window.ajaxurl || '',
        nonce: window.somecaptions_nonce || '',
        i18n: window.somecaptions_i18n || {}
    });
});
