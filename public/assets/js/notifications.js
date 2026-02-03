$(document).ready(function() {
    let notificationCheckInterval;

    // Load notification count and latest notifications on page load
    loadNotificationCount();
    loadLatestNotifications();

    // Set up interval to check for new notifications every 30 seconds
    notificationCheckInterval = setInterval(function() {
        loadNotificationCount();
        loadLatestNotifications();
    }, 30000);

    // Load notification count
    function loadNotificationCount() {
        $.ajax({
            url: '/notifications/unread-count',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const count = response.count;
                const badge = $('#notificationBadge');
                
                if (count > 0) {
                    badge.text(count > 99 ? '99+' : count).show();
                } else {
                    badge.hide();
                }
            },
            error: function(xhr) {
                console.error('Error loading notification count:', xhr);
            }
        });
    }

    // Load latest notifications
    function loadLatestNotifications() {
        $.ajax({
            url: '/notifications/latest',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const notifications = response.notifications;
                const listContainer = $('#notificationsList');
                
                if (notifications.length === 0) {
                    listContainer.html('<div class="text-center p-4 text-muted">No notifications</div>');
                    return;
                }

                let html = '';
                notifications.forEach(function(notification) {
                    const isRead = notification.read_at !== null;
                    const bgClass = isRead ? '' : 'bg-light';
                    html += `
                        <div class="notification-item ${bgClass} p-3 border-bottom" data-id="${notification.id}" style="cursor: pointer; transition: background-color 0.2s;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <p class="mb-1" style="font-size: 14px; color: #333;">${notification.message}</p>
                                    <small class="text-muted" style="font-size: 12px;">${notification.created_at}</small>
                                </div>
                                ${!isRead ? '<span class="badge bg-primary rounded-pill ms-2" style="font-size: 8px;">New</span>' : ''}
                            </div>
                        </div>
                    `;
                });
                
                listContainer.html(html);

                // Add click handler to notification items
                $('.notification-item').on('click', function() {
                    const notificationId = $(this).data('id');
                    const url = notifications.find(n => n.id === notificationId)?.url || '#';
                    
                    // Mark as read
                    markNotificationAsRead(notificationId);
                    
                    // Navigate to URL
                    if (url !== '#') {
                        window.location.href = url;
                    }
                });
            },
            error: function(xhr) {
                console.error('Error loading notifications:', xhr);
                $('#notificationsList').html('<div class="text-center p-4 text-danger">Error loading notifications</div>');
            }
        });
    }

    // Mark notification as read
    function markNotificationAsRead(notificationId) {
        $.ajax({
            url: `/notifications/${notificationId}/read`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Reload notifications and count
                    loadNotificationCount();
                    loadLatestNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking notification as read:', xhr);
            }
        });
    }

    // Mark all as read
    $('#markAllReadBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/notifications/mark-all-read',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadNotificationCount();
                    loadLatestNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking all as read:', xhr);
            }
        });
    });

    // View more notifications
    $('#viewMoreNotifications').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Create a modal or redirect to a notifications page
        // For now, we'll show all notifications in a modal
        showAllNotificationsModal();
    });

    // Show all notifications in a modal
    function showAllNotificationsModal() {
        $.ajax({
            url: '/notifications',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Handle both paginated and non-paginated responses
                let notifications;
                if (response.notifications && response.notifications.data) {
                    notifications = response.notifications.data;
                } else if (Array.isArray(response.notifications)) {
                    notifications = response.notifications;
                } else {
                    notifications = [];
                }
                
                let modalHtml = `
                    <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="allNotificationsModalLabel">All Notifications</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                `;
                
                if (notifications.length === 0) {
                    modalHtml += '<div class="text-center p-4 text-muted">No notifications</div>';
                } else {
                    notifications.forEach(function(notification) {
                        const isRead = notification.read_at !== null;
                        const bgClass = isRead ? '' : 'bg-light';
                        modalHtml += `
                            <div class="notification-item ${bgClass} p-3 border-bottom mb-2" data-id="${notification.id}" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-1" style="font-size: 14px; color: #333;">${notification.message}</p>
                                        <small class="text-muted" style="font-size: 12px;">${notification.created_at_human || notification.created_at}</small>
                                    </div>
                                    ${!isRead ? '<span class="badge bg-primary rounded-pill ms-2">New</span>' : ''}
                                </div>
                            </div>
                        `;
                    });
                }
                
                modalHtml += `
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                $('#allNotificationsModal').remove();
                
                // Append modal to body
                $('body').append(modalHtml);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('allNotificationsModal'));
                modal.show();
                
                // Add click handler to notification items in modal
                $('#allNotificationsModal .notification-item').on('click', function() {
                    const notificationId = $(this).data('id');
                    const notification = notifications.find(n => n.id === notificationId);
                    
                    if (notification && notification.url && notification.url !== '#') {
                        markNotificationAsRead(notificationId);
                        window.location.href = notification.url;
                    } else {
                        markNotificationAsRead(notificationId);
                    }
                });
            },
            error: function(xhr) {
                console.error('Error loading all notifications:', xhr);
                alert('Error loading notifications');
            }
        });
    }

    // Refresh notifications when dropdown is opened
    $('#notificationDropdown').on('show.bs.dropdown', function() {
        loadLatestNotifications();
    });
});

