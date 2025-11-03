/**
 * Mobile Navigation Handler for Parker CRM
 * Handles sidebar toggle functionality for mobile devices
 */

document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const mobileSidebar = document.querySelector('.sidebar-container-for-mobile-view');
    const body = document.body;
    
    // Check if elements exist
    if (!navbarToggler || !mobileSidebar) {
        console.warn('Mobile navigation elements not found');
        return;
    }
    
    // Toggle sidebar function
    function toggleSidebar() {
        if (mobileSidebar.classList.contains('sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }
    
    // Open sidebar function
    function openSidebar() {
        mobileSidebar.classList.add('sidebar-open');
        body.style.overflow = 'hidden'; // Prevent background scrolling
        body.classList.add('sidebar-open');
        
        // Add overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(overlay);
        
        // Fade in overlay
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
        
        // Close sidebar when overlay is clicked
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Close sidebar function
    function closeSidebar() {
        mobileSidebar.classList.remove('sidebar-open');
        body.style.overflow = '';
        body.classList.remove('sidebar-open');
        
        // Remove overlay
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }
    }
    
    // Add click event to navbar toggler
    navbarToggler.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });
    
    // Close sidebar when window is resized to desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    // Close sidebar when escape key is pressed
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileSidebar.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });
    
    // Handle dropdown toggles in mobile sidebar
    const mobileDropdownToggles = mobileSidebar.querySelectorAll('.nav-link.dropdown-toggle');
    mobileDropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.nextElementSibling;
            const isOpen = dropdown.classList.contains('show');
            
            // Close all other dropdowns
            mobileSidebar.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            if (isOpen) {
                dropdown.classList.remove('show');
            } else {
                dropdown.classList.add('show');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!mobileSidebar.contains(e.target)) {
            mobileSidebar.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});

/**
 * Table Responsiveness Helper
 * Improves table behavior on mobile devices
 */
document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.table-responsive table');
    
    tables.forEach(table => {
        // Add horizontal scroll indicator for mobile
        if (window.innerWidth <= 768) {
            const wrapper = table.closest('.table-responsive');
            if (wrapper) {
                wrapper.style.position = 'relative';
                
                // Add scroll indicator
                const indicator = document.createElement('div');
                indicator.className = 'scroll-indicator';
                indicator.innerHTML = '← Scroll to see more →';
                indicator.style.cssText = `
                    position: absolute;
                    bottom: -25px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 15px;
                    font-size: 12px;
                    z-index: 10;
                    opacity: 0.8;
                `;
                
                wrapper.appendChild(indicator);
                
                // Hide indicator after 3 seconds
                setTimeout(() => {
                    if (indicator.parentNode) {
                        indicator.style.opacity = '0';
                        setTimeout(() => {
                            if (indicator.parentNode) {
                                indicator.parentNode.removeChild(indicator);
                            }
                        }, 500);
                    }
                }, 3000);
            }
        }
    });
});

/**
 * Form Responsiveness Helper
 * Improves form behavior on mobile devices
 */
document.addEventListener('DOMContentLoaded', function() {
    // Improve select2 responsiveness
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('body') // Prevent dropdown from being cut off
        });
    }
    
    // Improve date picker responsiveness
    if (typeof $.fn.daterangepicker !== 'undefined') {
        $('.daterangepicker-input').daterangepicker({
            opens: window.innerWidth <= 768 ? 'left' : 'right',
            drops: window.innerWidth <= 768 ? 'up' : 'down'
        });
    }
});

/**
 * Chart Responsiveness Helper
 * Improves chart behavior on different screen sizes
 */
function resizeCharts() {
    if (typeof Chart !== 'undefined') {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }
}

// Resize charts on window resize
window.addEventListener('resize', function() {
    clearTimeout(window.chartResizeTimeout);
    window.chartResizeTimeout = setTimeout(resizeCharts, 250);
});

/**
 * Utility function to detect mobile devices
 */
function isMobile() {
    return window.innerWidth <= 768;
}

/**
 * Utility function to detect touch devices
 */
function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}

// Add touch-friendly classes to body
if (isTouchDevice()) {
    document.body.classList.add('touch-device');
}

// Add mobile class to body for mobile-specific styling
if (isMobile()) {
    document.body.classList.add('mobile-device');
}
