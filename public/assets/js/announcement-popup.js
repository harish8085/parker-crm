$(document).ready(function () {
    fetchAnnouncements();
});

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

        $('#announcementModalTitle').text(announcement.title);
        $('#announcementModalBody').text(announcement.message);

        const modalElement = document.getElementById('announcementModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false,
        });

        $('#announcementOkButton')
            .off('click')
            .on('click', function () {
                acknowledgeAnnouncement(announcement.id, function () {
                    modal.hide();
                    index++;
                    showNext();
                });
            });

        modal.show();
    }

    showNext();
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


