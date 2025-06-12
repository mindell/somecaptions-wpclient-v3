/**
 * Tab Navigation Fix
 * 
 * This script fixes tab navigation issues by directly attaching click handlers
 * to tab elements and ensuring they work properly.
 */

(function() {
    'use strict';
    
    // Run immediately when script loads
    fixTabNavigation();
    
    // Also run when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', fixTabNavigation);
    
    /**
     * Fix tab navigation by directly attaching click handlers
     */
    function fixTabNavigation() {
        console.log('Tab fix script running...');
        
        // Get all tab elements
        const tabLinks = document.querySelectorAll('.somecaptions-nav-tab');
        const tabPanels = document.querySelectorAll('.somecaptions-tab-panel');
        
        if (!tabLinks.length || !tabPanels.length) {
            console.log('No tabs found yet, will try again');
            // Try again in a moment if tabs aren't loaded yet
            setTimeout(fixTabNavigation, 500);
            return;
        }
        
        console.log('Found tabs:', tabLinks.length);
        
        // Add direct click handlers to each tab
        tabLinks.forEach(tab => {
            // Remove old click handlers by cloning
            const newTab = tab.cloneNode(true);
            if (tab.parentNode) {
                tab.parentNode.replaceChild(newTab, tab);
            }
            
            // Add new click handler
            newTab.addEventListener('click', function(e) {
                e.preventDefault();
                
                const tabId = this.getAttribute('data-tab');
                console.log('Tab clicked:', tabId);
                
                // Remove active class from all tabs
                tabLinks.forEach(t => t.classList.remove('somecaptions-nav-tab-active'));
                
                // Add active class to clicked tab
                this.classList.add('somecaptions-nav-tab-active');
                
                // Hide all panels
                tabPanels.forEach(panel => panel.classList.add('somecaptions-hidden'));
                
                // Show selected panel
                const panel = document.getElementById(tabId);
                if (panel) {
                    panel.classList.remove('somecaptions-hidden');
                }
                
                // Update URL hash without scrolling
                history.replaceState(null, null, '#' + tabId);
                
                // Store active tab in session storage
                sessionStorage.setItem('somecaptions_active_tab', tabId);
            });
        });
        
        // Also fix the "Verify Domain" button in the status card
        const verifyDomainButton = document.querySelector('.somecaptions-nav-tab-trigger');
        if (verifyDomainButton) {
            const newButton = verifyDomainButton.cloneNode(true);
            if (verifyDomainButton.parentNode) {
                verifyDomainButton.parentNode.replaceChild(newButton, verifyDomainButton);
            }
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const tabId = this.getAttribute('data-tab');
                console.log('Verify domain button clicked, switching to tab:', tabId);
                
                // Find the tab link and simulate a click
                const tabLink = document.querySelector(`.somecaptions-nav-tab[data-tab="${tabId}"]`);
                if (tabLink) {
                    tabLink.click();
                } else {
                    // Fallback if tab link not found
                    tabLinks.forEach(t => t.classList.remove('somecaptions-nav-tab-active'));
                    tabPanels.forEach(panel => panel.classList.add('somecaptions-hidden'));
                    
                    const panel = document.getElementById(tabId);
                    if (panel) {
                        panel.classList.remove('somecaptions-hidden');
                    }
                }
            });
        }
        
        // Check for hash in URL and activate corresponding tab
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabLink = document.querySelector(`.somecaptions-nav-tab[data-tab="${hash}"]`);
            if (tabLink) {
                console.log('Found hash in URL, activating tab:', hash);
                tabLink.click();
            }
        }
    }
})();
