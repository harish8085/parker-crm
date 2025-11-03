<script>
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this sales?')) {
            var userId = $(this).data('sale-id');
            $.ajax({
                url: '/sales-person/delete/' + userId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Sales deleted successfully.');
                    // Reload the page or update the table to reflect the changes
                    location.reload();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
</script>


<!-- Date picker -->
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize the date range picker
        $('#date-range-picker').daterangepicker({
            opens: 'right',

            locale: {
                format: 'YYYY-MM-DD',
                separator: ' to '
            }
        });

        // Show or hide the date range picker based on the selected option
        $('#date').on('change', function() {
            var val = this.value;
            if (val == 'custom') {
                $('.date_range').show(); // Show date range picker
            } else {
                $('.date_range').hide(); // Hide date range picker
            }
        });
    });
</script>

<!-- Datatable -->
<script type="text/javascript">
            $.fn.dataTable.ext.errMode = 'none';

    function load_data(date = '', date_range = '', name = '') {

        var table = $('.data-table').DataTable({
            debug: false, // Disable debugging
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ], // Options for the "Show entries" dropdown
            buttons: [{
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Sales Person',
                    charset: 'UTF-8',
                    bom: true,
                    title: function() {
                        return name ? name + ' Sales Person Details ' : 'All Sales Person Details';
                    },
                    exportOptions: {
                        columns: function(index, data, node) {
                            // Exclude the action column (assuming it's the last column)
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function(csv) {
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                        }
                        if (date || date_range || name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (name) header += 'Sales Person: ' + name + '\n';
                        }
                        return header + csv; // Prepend the filter information to the CSV content
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: function() {
                        return name ? name + ' Sales Person Details ' : 'All Sales Person Details';
                    },
                    exportOptions: {
                        columns: function(index, data, node) {
                            // Exclude the action column (assuming it's the last column)
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml']; // Access the sheet XML
                        // Construct the custom header
                        var header = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                        }
                        if (date || date_range || name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (name) header += 'Sales Person: ' + name + '\n';
                        }
                        // Add the header in the first row
                        var rows = $('row', sheet); // Get all rows
                        var firstRow = rows[0]; // Access the first row
                        var newRow = '<row r="1">' +
                            '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                            '</row><row r="2">' +
                            '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                            '</row>';

                        $(firstRow).before(newRow); // Insert the custom header row before the first row
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: function() {
                        return name ? name + ' Sales Person Details ' : 'All Sales Person Details';
                    },
                    exportOptions: {
                        columns: function(index, data, node) {
                            // Exclude the action column (assuming it's the last column)
                            return index !== table.column(':last').index();
                        }
                    },
                    customize: function(win) {
                        var filters = '';
                        var date = $("#date option:selected").html();
                        if (date == "custom") {
                            var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                        }
                        if (date || date_range || name) {
                            filters += '<h4>Filters Applied:</h4>';
                            if (date) filters += '<p>Date: ' + date + '</p>';
                            if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                            if (name) filters += '<p>Sales Person: ' + name + '</p>';
                        }

                        $(win.document.body).prepend(filters);
                    }
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales-person.index') }}",
                data: {
                    date: date,
                    date_range: date_range,
                    name: name,
                },
                error: function(xhr, error, thrown) {
                    console.log(xhr.responseText);
                },
            },
            columns: [{
                    data: null,
                    name: 'srno',
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'Emp_Id',
                    name: 'Emp_Id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

    };


    $(document).ready(function() {
        load_data();

        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#filter').click(function() {
            var date = $('#date').val();
            var date_range = $('#date-range-picker').val();
            var name = $('#name').val();

            if (date || name) {
                $('.data-table').DataTable().destroy();
                load_data(date, date_range, name);
            } else {
                alert('Select at least one filter!');
            }
        });

        $('#refresh').click(function() {
            window.location.reload();
        });
    });
</script>