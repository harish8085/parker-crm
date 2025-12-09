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

    function load_data(date = '', date_range = '', user_name = '') {
        var table = $('.data-table').DataTable({
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
                        return 'Announcement Logs - {{ $announcement->title }}';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            return true;
                        }
                    },
                    customize: function (csv) {
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || user_name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (user_name) header += 'User Name: ' + user_name + '\n';
                        }
                        return header + csv;
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: function () {
                        return 'Announcement Logs - {{ $announcement->title }}';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            return true;
                        }
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || user_name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (user_name) header += 'User Name: ' + user_name + '\n';
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
                        return 'Announcement Logs - {{ $announcement->title }}';
                    },
                    exportOptions: {
                        columns: function (index, data, node) {
                            return true;
                        }
                    },
                    customize: function (win) {
                        var filters = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val();
                        }
                        if (date || date_range || user_name) {
                            filters += '<h4>Filters Applied:</h4>';
                            if (date) filters += '<p>Date: ' + date + '</p>';
                            if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                            if (user_name) filters += '<p>User Name: ' + user_name + '</p>';
                        }
                        $(win.document.body).prepend(filters);
                    }
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('announcements.logs', $announcement->id) }}",
                data: {
                    date: date,
                    date_range: date_range,
                    user_name: user_name,
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
                { data: 'user_name', name: 'user_name' },
                { data: 'user_email', name: 'user_email' },
                { data: 'viewed_at', name: 'viewed_at' },
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
            var user_name = $('#user_name').val();

            $('.data-table').DataTable().destroy();
            load_data(date, date_range, user_name);
        });

        $('#refresh').click(function () {
            window.location.reload();
        });
    });
</script>

