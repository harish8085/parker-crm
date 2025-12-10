<script>
    $(document).on('click', '.delete-announcement-btn', function () {
        if (confirm('Are you sure you want to delete this announcement?')) {
            var id = $(this).data('announcement-id');
            
            $.ajax({
                url: '/announcements/delete/' + id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Refresh the DataTable if it exists
                    if (announcementTable) {
                        announcementTable.ajax.reload(null, false);
                    } else {
                        // Fallback: try to get the table instance
                        var table = $('.data-table').DataTable();
                        if (table) {
                            table.ajax.reload(null, false);
                        } else {
                            // Last resort: reload the page
                            location.reload();
                        }
                    }
                    
                    // Show success message
                    if (response && response.message) {
                        alert(response.message);
                    } else {
                        alert('Announcement deleted successfully.');
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert('Error deleting announcement. Please try again.');
                }
            });
        }
    });
</script>

<!-- Date picker -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#date-range-picker').daterangepicker({
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' to '
            }
        });

        $('#date').on('change', function () {
            var val = this.value;
            if (val == 'custom') {
                $('.date_range').show();
            } else {
                $('.date_range').hide();
            }
        });
    });
</script>

<!-- Datatable -->
<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    
    // Store DataTable instance globally
    var announcementTable = null;

    function load_data(date = '', date_range = '', title = '') {
        announcementTable = $('.data-table').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    charset: 'UTF-8',
                    bom: true,
                    title: function () {
                        return title ? title + ' Announcement Details ' : 'Announcements Details';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            var table = this.api();
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function (csv) {
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || title) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (title) header += 'Title: ' + title + '\n';
                        }
                        return header + csv;
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: function () {
                        return title ? title + ' Announcement Details ' : 'Announcements Details';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            var table = this.api();
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || title) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (title) header += 'Title: ' + title + '\n';
                        }
                        var rows = $('row', sheet);
                        var firstRow = rows[0];
                        var newRow = '<row r="1">' +
                            '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                            '</row><row r="2">' +
                            '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                            '</row>';
                        $(firstRow).before(newRow);
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: function () {
                        return title ? title + ' Announcement Details ' : 'Announcements Details';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            var table = this.api();
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function (win) {
                        var filters = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || title) {
                            filters += '<h4>Filters Applied:</h4>';
                            if (date) filters += '<p>Date: ' + date + '</p>';
                            if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                            if (title) filters += '<p>Title: ' + title + '</p>';
                        }
                        $(win.document.body).prepend(filters);
                    }
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('announcements.index') }}",
                data: {
                    date: date,
                    date_range: date_range,
                    title: title,
                },
                error: function (xhr, error, thrown) {
                    console.log(xhr.responseText);
                },
            },
            columns: [
                {
                    data: null,
                    name: 'srno',
                    render: function (data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'title', name: 'title' },
                { data: 'message', name: 'message' },
                { data: 'starts_at', name: 'starts_at' },
                { data: 'expires_at', name: 'expires_at' },
                { data: 'is_active', name: 'is_active' },
                { data: 'views_count', name: 'views_count' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
    }

    $(document).ready(function () {
        load_data();

        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#filter').click(function () {
            var date = $('#date').val();
            var date_range = $('#date-range-picker').val();
            var title = $('#title').val();

            if (date || title) {
                if (announcementTable) {
                    announcementTable.destroy();
                }
                load_data(date, date_range, title);
            } else {
                alert('Select at least one filter!');
            }
        });

        $('#refresh').click(function () {
            if (announcementTable) {
                announcementTable.ajax.reload(null, false);
            } else {
                window.location.reload();
            }
        });
    });
</script>


