<script>
    $(document).on('change', '.status-toggle', function () {
        var bankId = $(this).data('bank-id');
        var isChecked = $(this).is(':checked');
        var action = isChecked ? 'activate' : 'deactivate';
        var actionText = isChecked ? 'activate' : 'deactivate';
        var toggleElement = $(this);
        
        bootbox.confirm({
            message: 'Are you sure you want to ' + actionText + ' this bank account?',
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-secondary'
                }
            },
            callback: function (result) {
                if (result) {
                    var url = isChecked ? '/link-bank/activate/' + bankId : '/link-bank/deactivate/' + bankId;
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            bootbox.alert({
                                message: 'Bank account ' + actionText + 'd successfully.',
                                callback: function () {
                                    $('.data-table').DataTable().ajax.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            // Revert the toggle if there's an error
                            var toggle = $('.status-toggle[data-bank-id="' + bankId + '"]');
                            toggle.prop('checked', !isChecked);
                            
                            console.log(xhr.responseText);
                            bootbox.alert({
                                message: 'An error occurred while ' + actionText + 'ing the bank account.',
                                className: 'bootbox-danger'
                            });
                        }
                    });
                } else {
                    // Revert the toggle if user cancels
                    toggleElement.prop('checked', !isChecked);
                }
            }
        });
    });

</script>

<!-- Date picker -->
<script type="text/javascript">
    $(document).ready(function () {
        // Initialize the date range picker
        $('#date-range-picker').daterangepicker({
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' to '
            }
        });

        // Show or hide the date range picker based on the selected option
        $('#date').on('change', function () {
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
    function load_data(date = '', date_range = '', bank_name = '', status = '') {

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
                charset: 'UTF-8',
                bom: true,
                title: function () {
                    return bank_name ? bank_name + ' Bank Account Details ' : 'Bank Account Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (csv) {
                    var header = '';
                    var date = $("#date option:selected").html();
                    if (date == "custom") {
                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                    }
                    if (date || date_range || bank_name || status) {
                        if (date) header += 'Date: ' + date + '\n';
                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                        if (bank_name) header += 'Bank Name: ' + bank_name + '\n';
                        if (status) header += 'Status: ' + (status == 1 ? 'Active' : 'Inactive') + '\n';
                    }
                    return header + csv; // Prepend the filter information to the CSV content
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: function () {
                    return bank_name ? bank_name + ' Bank Account Details ' : 'Bank Account Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml']; // Access the sheet XML
                    // Construct the custom header
                    var header = '';
                    var date = $("#date option:selected").html();
                    if (date == "custom") {
                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                    }
                    if (date || date_range || bank_name || status) {
                        if (date) header += 'Date: ' + date + '\n';
                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                        if (bank_name) header += 'Bank Name: ' + bank_name + '\n';
                        if (status) header += 'Status: ' + (status == 1 ? 'Active' : 'Inactive') + '\n';
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
                title: function () {
                    return bank_name ? bank_name + ' Bank Account Details ' : 'Bank Account Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (win) {
                    var filters = '';
                    var date = $("#date option:selected").html();
                    if (date == "custom") {
                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                    }
                    if (date || date_range || bank_name || status) {
                        filters += '<h4>Filters Applied:</h4>';
                        if (date) filters += '<p>Date: ' + date + '</p>';
                        if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                        if (bank_name) filters += '<p>Bank Name: ' + bank_name + '</p>';
                        if (status) filters += '<p>Status: ' + (status == 1 ? 'Active' : 'Inactive') + '</p>';
                    }

                    $(win.document.body).prepend(filters);
                }
            },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('link-bank.index') }}",
                data: {
                    date: date,
                    date_range: date_range,
                    bank_name: bank_name,
                    status: status,
                },
                error: function (xhr, error, thrown) {
                    console.log(xhr.responseText);
                },
            },
            columns: [{
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false
            },
            {
                data: 'account_holder_name',
                name: 'account_holder_name'
            },
            {
                data: 'account_number',
                name: 'account_number'
            },
            {
                data: 'ifsc_code',
                name: 'ifsc_code'
            },
            {
                data: 'bank_name',
                name: 'bank_name'
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

    $(document).ready(function () {
        load_data();

        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#filter').click(function () {
            var date = $('#date').val();
            var date_range = $('#date-range-picker').val();
            var bank_name = $('#bank_name').val();
            var status = $('#status').val();

            if (date || bank_name || status) {
                $('.data-table').DataTable().destroy();
                load_data(date, date_range, bank_name, status);
            } else {
                bootbox.alert({
                    message: 'Select at least one filter!',
                    className: 'bootbox-warning'
                });
            }
        });

        $('#refresh').click(function () {
            window.location.reload();
        });
    });
</script>

