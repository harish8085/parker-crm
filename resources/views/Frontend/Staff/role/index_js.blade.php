<script>
    $(document).on('click', '.delete-role-btn', function() {
        if (confirm('Are you sure you want to delete this role?')) {
            var roleId = $(this).data('role-id');
            $.ajax({
                url: '/staff/delete/role/' + roleId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Role deleted successfully.');
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

<script>
    $(document).on('click', '.edit-role-btn', function() {
        var roleId = $(this).data('role-id');
        $.get('/staff/update/role/' + roleId, function(response) {
            $('#editRoleName').val(response.name);
            $('#editStatus').val(response.status);
            // Populate other form fields as needed
            $('#editRoleForm').attr('action', '/staff/update/role/' + roleId);
        });
    });
</script>

<script>
    $(document).ready(function() {

        $('.view-role-btn').click(function() {
            var roleId = $(this).data('role-id');
            $.get('/staff/view/role/' + roleId, function(response) {
                $('#viewRoleName').val(response.name);
                $('#viewStatus').val(response.status);
            });
        });
        $('#editRoleForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'PUT',
                data: formData,
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
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
                    title: 'Roles Details',
                    charset: 'UTF-8',
                    bom: true,
                    title: function() {
                        return name ? name + ' Details ' : 'All Roles Details';
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
                            if (name) header += 'Name: ' + name + '\n';
                        }
                        return header + csv; // Prepend the filter information to the CSV content
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: function() {
                        return name ? name + 'Details ' : 'All Roles Details';
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
                            if (name) header += 'Name: ' + name + '\n';
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
                        return name ? name + 'Details ' : 'All Roles Details';
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
                            if (name) filters += '<p>Name: ' + name + '</p>';
                        }

                        $(win.document.body).prepend(filters);
                    }
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('manage-role.index') }}",
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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'role_id',
                    name: 'role_id'
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