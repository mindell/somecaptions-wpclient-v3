/**
 * SoMe Captions Client Admin Scripts
 * 
 * Main JavaScript file for the SoMe Captions Client admin interface
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initTabNavigation();
        initNotificationSystem();
        initUIInteractions();
    });
    
    // We no longer need to listen for tab updates since all tabs are static

    /**
     * Initialize tab navigation
     */
    function initTabNavigation() {
        console.log('Initializing tab navigation');
        const tabLinks = document.querySelectorAll('.somecaptions-nav-tab');
        const tabPanels = document.querySelectorAll('.somecaptions-tab-panel');
        
        console.log('Found tabs:', tabLinks.length, 'and panels:', tabPanels.length);
        
        if (!tabLinks.length || !tabPanels.length) return;
        
        // Check for stored active tab
        const storedTab = sessionStorage.getItem('somecaptions_active_tab');
        let activeTab = 'general-settings'; // Default tab
        
        if (storedTab) {
            // Check if the stored tab exists in the current view
            const tabExists = Array.from(tabLinks).some(tab => tab.getAttribute('data-tab') === storedTab);
            if (tabExists) {
                activeTab = storedTab;
            }
        }
        
        console.log('Setting active tab to:', activeTab);
        
        // Set initial active tab
        setActiveTab(activeTab);
        
        // IMPORTANT: Instead of cloning, let's directly attach event listeners
        tabLinks.forEach((tabLink, index) => {
            // First, remove the element from the DOM
            const parent = tabLink.parentNode;
            const nextSibling = tabLink.nextSibling;
            parent.removeChild(tabLink);
            
            // Then add it back to force a fresh state
            if (nextSibling) {
                parent.insertBefore(tabLink, nextSibling);
            } else {
                parent.appendChild(tabLink);
            }
            
            // Now add the event listener
            tabLink.addEventListener('click', function(e) {
                console.log('Tab clicked:', this.getAttribute('data-tab'));
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                setActiveTab(tabId);
                
                // Store the active tab in session storage
                sessionStorage.setItem('somecaptions_active_tab', tabId);
            });
            
            console.log('Added click listener to tab:', tabLink.getAttribute('data-tab'));
        });
        
        // Also handle the "Verify Domain" button in the status card
        const verifyDomainButton = document.querySelector('.somecaptions-nav-tab-trigger');
        if (verifyDomainButton) {
            verifyDomainButton.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                setActiveTab(tabId);
                sessionStorage.setItem('somecaptions_active_tab', tabId);
            });
        }
        
        // Handle hash changes (for direct links to tabs)
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const tabExists = Array.from(document.querySelectorAll('.somecaptions-nav-tab')).some(tab => tab.getAttribute('data-tab') === hash);
                if (tabExists) {
                    setActiveTab(hash);
                }
            }
        });
        
        // Check initial hash
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabExists = Array.from(document.querySelectorAll('.somecaptions-nav-tab')).some(tab => tab.getAttribute('data-tab') === hash);
            if (tabExists) {
                setActiveTab(hash);
            }
        }
    }
    
    /**
     * Set the active tab
     * 
     * @param {string} tabId - The ID of the tab to activate
     */
    function setActiveTab(tabId) {
        console.log('Setting active tab:', tabId);
        const tabLinks = document.querySelectorAll('.somecaptions-nav-tab');
        const tabPanels = document.querySelectorAll('.somecaptions-tab-panel');
        
        console.log('Found', tabLinks.length, 'tabs and', tabPanels.length, 'panels');
        
        // Remove active class from all tabs and panels
        tabLinks.forEach(tab => {
            tab.classList.remove('somecaptions-nav-tab-active');
            console.log('Removed active class from tab:', tab.getAttribute('data-tab'));
        });
        
        tabPanels.forEach(panel => {
            panel.classList.add('somecaptions-hidden');
            console.log('Added hidden class to panel:', panel.id);
        });
        
        // Add active class to selected tab and panel
        const activeTab = document.querySelector(`.somecaptions-nav-tab[data-tab="${tabId}"]`);
        const activePanel = document.getElementById(tabId);
        
        console.log('Active tab element:', activeTab ? 'found' : 'not found');
        console.log('Active panel element:', activePanel ? 'found' : 'not found');
        
        if (activeTab) {
            activeTab.classList.add('somecaptions-nav-tab-active');
            console.log('Added active class to tab:', tabId);
        }
        
        if (activePanel) {
            activePanel.classList.remove('somecaptions-hidden');
            console.log('Removed hidden class from panel:', tabId);
        } else {
            console.error('Could not find panel with ID:', tabId);
        }
        
        // Update URL hash
        window.location.hash = tabId;
        console.log('Updated URL hash to:', tabId);
    }

    /**
     * Initialize notification system
     */
    function initNotificationSystem() {
        // Create notification container if it doesn't exist
        let notificationContainer = document.querySelector('.somecaptions-notifications');
        
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.className = 'somecaptions-notifications';
            
            // Insert after admin header
            const adminHeader = document.querySelector('.somecaptions-admin-header');
            if (adminHeader && adminHeader.parentNode) {
                adminHeader.parentNode.insertBefore(notificationContainer, adminHeader.nextSibling);
            }
        }
        
        // Expose notification methods globally
        window.somecaptionsNotify = {
            show: function(message, type = 'info', duration = 5000, dismissible = true) {
                createNotification(message, type, duration, dismissible);
            },
            success: function(message, duration = 5000) {
                createNotification(message, 'success', duration, true);
            },
            error: function(message, duration = 0) {
                createNotification(message, 'error', duration, true);
            },
            info: function(message, duration = 5000) {
                createNotification(message, 'info', duration, true);
            },
            warning: function(message, duration = 8000) {
                createNotification(message, 'warning', duration, true);
            },
            clear: function() {
                notificationContainer.innerHTML = '';
            }
        };
    }
    
    /**
     * Create and display a notification
     * 
     * @param {string} message - The notification message
     * @param {string} type - Notification type (success, error, info, warning)
     * @param {number} duration - How long to display the notification (0 = indefinitely)
     * @param {boolean} dismissible - Whether the notification can be dismissed
     */
    function createNotification(message, type, duration, dismissible) {
        const container = document.querySelector('.somecaptions-notifications');
        if (!container) return;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `somecaptions-notification somecaptions-notification-${type}`;
        
        // Add appropriate icon based on type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '\u2713';
                break;
            case 'error':
                icon = '\u2717';
                break;
            case 'warning':
                icon = '\u26a0';
                break;
            default:
                icon = '\u2139';
        }
        
        // Create notification content
        notification.innerHTML = `
            <span class="somecaptions-notification-icon">${icon}</span>
            <span class="somecaptions-notification-message">${message}</span>
            ${dismissible ? '<button class="somecaptions-notification-dismiss" aria-label="Dismiss">\u00d7</button>' : ''}
        `;
        
        // Add to container
        container.appendChild(notification);
        
        // Add dismiss functionality
        if (dismissible) {
            const dismissButton = notification.querySelector('.somecaptions-notification-dismiss');
            if (dismissButton) {
                dismissButton.addEventListener('click', function() {
                    notification.classList.add('somecaptions-notification-fade');
                    setTimeout(() => {
                        if (container.contains(notification)) {
                            container.removeChild(notification);
                        }
                    }, 300);
                });
            }
        }
        
        // Auto-dismiss after duration (if set)
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.add('somecaptions-notification-fade');
                setTimeout(() => {
                    if (container.contains(notification)) {
                        container.removeChild(notification);
                    }
                }, 300);
            }, duration);
        }
        
        // Accessibility
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'polite');
    }

    /**
     * Initialize general UI interactions
     */
    function initUIInteractions() {
        // Initialize collapsible sections
        initCollapsibleSections();
        
        // Initialize tooltips
        initTooltips();
    }
    
    /**
     * Initialize collapsible sections
     */
    function initCollapsibleSections() {
        const collapsibleHeaders = document.querySelectorAll('.somecaptions-collapsible-header');
        
        collapsibleHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const section = this.closest('.somecaptions-collapsible');
                if (section) {
                    section.classList.toggle('somecaptions-collapsed');
                    
                    // Update aria attributes for accessibility
                    const isCollapsed = section.classList.contains('somecaptions-collapsed');
                    this.setAttribute('aria-expanded', !isCollapsed);
                    
                    const content = section.querySelector('.somecaptions-collapsible-content');
                    if (content) {
                        content.setAttribute('aria-hidden', isCollapsed);
                    }
                }
            });
        });
    }
    
    /**
     * Initialize tooltips
     */
    function initTooltips() {
        const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
        
        tooltipTriggers.forEach(trigger => {
            // Create tooltip element
            const tooltip = document.createElement('span');
            tooltip.className = 'somecaptions-tooltip';
            tooltip.textContent = trigger.getAttribute('data-tooltip');
            tooltip.setAttribute('role', 'tooltip');
            tooltip.setAttribute('aria-hidden', 'true');
            
            // Add tooltip to the trigger element
            trigger.appendChild(tooltip);
            
            // Set trigger element attributes
            trigger.setAttribute('tabindex', '0');
            trigger.setAttribute('aria-describedby', 'tooltip');
            
            // Show tooltip on hover and focus
            trigger.addEventListener('mouseenter', () => {
                tooltip.setAttribute('aria-hidden', 'false');
            });
            
            trigger.addEventListener('focus', () => {
                tooltip.setAttribute('aria-hidden', 'false');
            });
            
            // Hide tooltip on mouse leave and blur
            trigger.addEventListener('mouseleave', () => {
                tooltip.setAttribute('aria-hidden', 'true');
            });
            
            trigger.addEventListener('blur', () => {
                tooltip.setAttribute('aria-hidden', 'true');
            });
        });
    }
})();
