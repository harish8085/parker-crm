$(document).ready(function () {
    fetchAnnouncements();
});

setInterval(fetchAnnouncements, 5000);

function fetchAnnouncements() {
    $.ajax({
        url: '/announcements/active',
        type: 'GET',
        success: function (response) {
            if (response.status === 'success' && response.data && response.data.length > 0) {
                showAnnouncementsSequentially(response.data);
            }
        },
    });
}

function showAnnouncementsSequentially(announcements) {
    let index = 0;

    function showNext() {
        if (index >= announcements.length) return;

        const announcement = announcements[index];

        // Set the title with icon
        $('#announcementModalTitle').html('<i class="fas fa-bullhorn me-2"></i>' + (announcement.title || 'Announcement'));
        
        // Build modal body with message and attachments
        let modalBodyHtml = '<div class="announcement-content">';
        
        // Display message (with HTML support)
        modalBodyHtml += '<div class="announcement-message mb-3">' + announcement.message + '</div>';
        
        // Display attachments if any
        if (announcement.attachments && announcement.attachments.length > 0) {
            modalBodyHtml += '<div class="announcement-attachments mt-3">';
            modalBodyHtml += '<strong><i class="fas fa-paperclip me-2"></i>Attachments:</strong><div class="attachment-list mt-2">';
            
            announcement.attachments.forEach(function(attachment) {
                const ext = attachment.name.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
                
                modalBodyHtml += '<div class="attachment-item mb-2 d-flex align-items-center">';
                
                if (isImage) {
                    modalBodyHtml += '<div class="me-3 text-center">';
                    modalBodyHtml += '<img src="' + attachment.url + '" class="attachment-preview-img" style="max-width: 150px; max-height: 120px; border-radius: 5px; cursor: pointer; display: block; margin: 0 auto;" onclick="window.open(\'' + attachment.url + '\', \'_blank\')" title="Click to view full size">';
                    modalBodyHtml += '</div>';
                } else {
                    // Get icon based on file type
                    const iconMap = {
                        'pdf': 'fa-file-pdf',
                        'doc': 'fa-file-word',
                        'docx': 'fa-file-word',
                        'xls': 'fa-file-excel',
                        'xlsx': 'fa-file-excel',
                        'ppt': 'fa-file-powerpoint',
                        'pptx': 'fa-file-powerpoint',
                        'txt': 'fa-file-alt',
                        'csv': 'fa-file-csv'
                    };
                    const icon = iconMap[ext] || 'fa-file';
                    modalBodyHtml += '<div class="me-3 text-center" style="min-width: 60px;">';
                    modalBodyHtml += '<i class="fas ' + icon + '" style="font-size: 40px; color: #6c757d;"></i>';
                    modalBodyHtml += '</div>';
                }
                
                modalBodyHtml += '<div class="flex-grow-1">';
                modalBodyHtml += '<div class="attachment-name mb-2" style="font-weight: 500; word-break: break-word;">' + attachment.name + '</div>';
                modalBodyHtml += '<a href="' + attachment.url + '" download="' + attachment.name + '" class="btn btn-sm btn-primary" onclick="event.stopPropagation(); downloadAttachment(\'' + attachment.url + '\', \'' + attachment.name + '\'); return false;">';
                modalBodyHtml += '<i class="fas fa-download"></i> Download</a>';
                modalBodyHtml += '</div>';
                modalBodyHtml += '</div>';
            });
            
            modalBodyHtml += '</div></div>';
        }
        
        modalBodyHtml += '</div>';
        
        $('#announcementModalBody').html(modalBodyHtml);

        const modalElement = document.getElementById('announcementModal');
        
        // Ensure modal is properly initialized
        let modal = bootstrap.Modal.getInstance(modalElement);
        if (!modal) {
            modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false,
            });
        }

        $('#announcementOkButton')
            .off('click')
            .on('click', function () {
                acknowledgeAnnouncement(announcement.id, function () {
                    modal.hide();
                    index++;
                    setTimeout(function() {
                        showNext();
                    }, 300);
                });
            });

        // Show modal
        modal.show();
    }

    showNext();
}

// Function to download attachment
function downloadAttachment(url, filename) {
    // Create a temporary anchor element
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    
    // Clean up
    setTimeout(function() {
        document.body.removeChild(link);
    }, 100);
}

function acknowledgeAnnouncement(id, callback) {
    $.ajax({
        url: '/announcements/' + id + '/acknowledge',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function () {
            if (typeof callback === 'function') {
                callback();
            }
        },
    });
}


