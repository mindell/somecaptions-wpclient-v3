/**
 * SoMe Captions Client - Tab Navigation
 * 
 * A simple, robust tab navigation system that doesn't rely on cloning elements
 */

(function() {
    'use strict';
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initSimpleTabNavigation();
    });
    
    /**
     * Initialize simple tab navigation
     */
    function initSimpleTabNavigation() {
        console.log('Initializing simple tab navigation');
        
        // Get all tab links and panels
        const tabContainer = document.querySelector('.somecaptions-nav-tabs');
        const tabPanels = document.querySelectorAll('.somecaptions-tab-panel');
        
        if (!tabContainer || !tabPanels.length) {
            console.error('Tab container or panels not found');
            return;
        }
        
        // Use event delegation for tab clicks
        tabContainer.addEventListener('click', function(e) {
            // Find the closest tab link if we clicked on a child element
            const tabLink = e.target.closest('.somecaptions-nav-tab');
            
            // If we didn't click on a tab link, do nothing
            if (!tabLink) return;
            
            // Prevent default link behavior
            e.preventDefault();
            
            // Get the tab ID
            const tabId = tabLink.getAttribute('data-tab');
            console.log('Tab clicked:', tabId);
            
            // Set active tab
            setActiveTab(tabId);
            
            // Store the active tab in session storage
            sessionStorage.setItem('somecaptions_active_tab', tabId);
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
        
        // Check for stored active tab
        const storedTab = sessionStorage.getItem('somecaptions_active_tab');
        let activeTab = 'general-settings'; // Default tab
        
        if (storedTab) {
            // Check if the stored tab exists in the current view
            const tabExists = !!document.querySelector(`.somecaptions-nav-tab[data-tab="${storedTab}"]`);
            if (tabExists) {
                activeTab = storedTab;
            }
        }
        
        // Set initial active tab
        setActiveTab(activeTab);
        
        // Handle hash changes (for direct links to tabs)
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const tabExists = !!document.querySelector(`.somecaptions-nav-tab[data-tab="${hash}"]`);
                if (tabExists) {
                    setActiveTab(hash);
                }
            }
        });
        
        // Check initial hash
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabExists = !!document.querySelector(`.somecaptions-nav-tab[data-tab="${hash}"]`);
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
        
        // Get all tab links and panels
        const tabLinks = document.querySelectorAll('.somecaptions-nav-tab');
        const tabPanels = document.querySelectorAll('.somecaptions-tab-panel');
        
        // Remove active class from all tabs and panels
        tabLinks.forEach(tab => tab.classList.remove('somecaptions-nav-tab-active'));
        tabPanels.forEach(panel => panel.classList.add('somecaptions-hidden'));
        
        // Add active class to selected tab and panel
        const activeTab = document.querySelector(`.somecaptions-nav-tab[data-tab="${tabId}"]`);
        const activePanel = document.getElementById(tabId);
        
        if (activeTab) {
            activeTab.classList.add('somecaptions-nav-tab-active');
        }
        
        if (activePanel) {
            activePanel.classList.remove('somecaptions-hidden');
        }
        
        // Update URL hash without triggering a scroll
        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        } else {
            location.hash = '#' + tabId;
        }
    }
})();
