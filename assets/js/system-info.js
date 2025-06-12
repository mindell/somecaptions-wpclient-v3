/**
 * SoMe Captions Client - System Information Handler
 * 
 * Handles copying system information to clipboard for support purposes.
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const copyButton = document.getElementById('copy-system-info');
        
        if (copyButton) {
            copyButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get system info content
                const systemInfoContent = document.getElementById('system-info-content');
                
                if (!systemInfoContent) {
                    return;
                }
                
                // Create a temporary textarea to copy from
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = systemInfoContent.innerText;
                document.body.appendChild(tempTextArea);
                
                // Select and copy the text
                tempTextArea.select();
                
                try {
                    const successful = document.execCommand('copy');
                    
                    // Show success or error notification
                    if (successful) {
                        showNotification('success', somecaptionsSystemInfo.copySuccess);
                    } else {
                        showNotification('error', somecaptionsSystemInfo.copyError);
                    }
                } catch (err) {
                    showNotification('error', somecaptionsSystemInfo.copyError);
                }
                
                // Remove the temporary textarea
                document.body.removeChild(tempTextArea);
            });
        }
    });
    
    /**
     * Show a notification message
     * 
     * @param {string} type - The type of notification (success, error, info)
     * @param {string} message - The message to display
     */
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `somecaptions-notice somecaptions-notice-${type} somecaptions-notice-inline`;
        notification.setAttribute('role', 'alert');
        
        // Add message
        const paragraph = document.createElement('p');
        paragraph.textContent = message;
        notification.appendChild(paragraph);
        
        // Add close button
        const closeButton = document.createElement('button');
        closeButton.className = 'somecaptions-notice-dismiss';
        closeButton.setAttribute('aria-label', 'Dismiss notice');
        closeButton.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
        notification.appendChild(closeButton);
        
        // Add notification to the page
        const systemInfoSection = document.querySelector('.somecaptions-system-info');
        if (systemInfoSection) {
            // Insert after the copy button
            const copyButton = document.getElementById('copy-system-info');
            if (copyButton && copyButton.parentNode) {
                copyButton.parentNode.insertBefore(notification, copyButton.nextSibling);
            } else {
                systemInfoSection.appendChild(notification);
            }
            
            // Add event listener to close button
            closeButton.addEventListener('click', function() {
                notification.remove();
            });
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }
})();
