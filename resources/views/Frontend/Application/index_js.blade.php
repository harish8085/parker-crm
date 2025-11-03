<script>
        $(document).on('click', '.delete-btn', function() {
                if (confirm('Are you sure you want to delete this application?')) {
                        var applicationId = $(this).data('application-id');
                        $.ajax({
                                url: '/application/delete/' + applicationId,
                                type: 'DELETE',
                                headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                        alert('Application deleted successfully.');
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
                const $actionButton = $('#actionButton'); // Reference to the button to show/hide
                function updateButtonVisibility() {
                        const selectedCount = $('.rowCheckbox:checked').length;
                        if (selectedCount === 1) {
                                $actionButton.show(); // Show button if exactly one checkbox is selected
                        } else {
                                $actionButton.hide(); // Hide button otherwise
                        }
                }

                // Attach event handlers to row checkboxes
                $('#applicationsTable tbody').on('change', '.rowCheckbox', function() {
                        updateButtonVisibility();
                });

                // Optional: Update button visibility when master checkbox is used
                $('#masterCheckbox').on('change', function() {
                        updateButtonVisibility();
                });

                // Initialize with button hidden
                $actionButton.hide();
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

                // Master checkbox functionality
                $('#masterCheckbox').on('change', function() {
                        const isChecked = this.checked;
                        $('.rowCheckbox').prop('checked', isChecked);
                        $actionButton.show();
                });

                // Update master checkbox based on row checkboxes
                $('#applicationsTable tbody').on('change', '.rowCheckbox', function() {
                        const allChecked = $('.rowCheckbox').length === $('.rowCheckbox:checked').length;
                        $('#masterCheckbox').prop('checked', allChecked);
                        if ($('.rowCheckbox:checked').length == 0) {
                                $actionButton.hide();

                        } else {

                                $actionButton.show();
                        }

                });

                // Bulk delete functionality
                $('#bulkDeleteBtn').on('click', function() {
                        const selectedIds = $('.rowCheckbox:checked')
                                .map(function() {
                                        return $(this).val();
                                })
                                .get();

                        if (selectedIds.length === 0) {
                                alert('Please select at least one application.');
                                return;
                        }

                        if (!confirm('Are you sure you want to delete the selected applications?')) {
                                return;
                        }

                        // Send AJAX request to delete selected rows
                        $.ajax({
                                url: "{{ route('applications.bulk-delete') }}",
                                type: "POST",
                                data: {
                                        application_ids: selectedIds,
                                        _token: "{{ csrf_token() }}",
                                },
                                success: function(response) {
                                        alert(response.message);
                                        table.ajax.reload(); // Reload DataTable to reflect changes
                                },
                                error: function(xhr) {
                                        alert(xhr.responseJSON.message || 'An error occurred.');
                                },
                        });
                });
        });
</script>

<!-- Datatable -->
<script type="text/javascript">
        $.fn.dataTable.ext.errMode = 'none';
        var table;

        function load_data(date = '', date_range = '', partner_name = '', bank_name = '', product_name = '', status = '') {
                table = $('.data-table').DataTable({
                        debug: false, // Disable debugging
                        dom: 'Bfrtip<"bottom"l>', // 'l' adds the "Show entries" dropdown
                        lengthMenu: [
                                [25, 50, 100, 500, -1],
                                [25, 50, 100, 500, 'All']
                        ], // Options for the "Show entries" dropdown
                        buttons: [{
                                        extend: 'csvHtml5',
                                        text: 'CSV',
                                        charset: 'UTF-8',
                                        title: 'Bank Application Details',
                                        exportOptions: {
                                                columns: function(index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        bom: true,
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Application Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Application Details';
                                                } else if (product_name) {
                                                        return product_name + ' Application Details';
                                                } else {
                                                        return 'All Application Details';
                                                }
                                        },
                                        customize: function(csv) {
                                                var header = ''; // Initialize empty header

                                                // Fetch selected options and values
                                                var partner_name = $("#partner_name option:selected").html();
                                                var date = $("#date option:selected").html();
                                                if (date == "custom") {
                                                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                }
                                                var product_name = $("#product_name").val();
                                                var status = $("#status").val();

                                                // Conditionally append filters to the header
                                                if (date) {
                                                        header += 'Date: ' + date + '\n';
                                                }
                                                if (date_range) {
                                                        header += 'Date Range: ' + date_range + '\n';
                                                }
                                                if (partner_name) {
                                                        header += 'Partner: ' + partner_name + '\n';
                                                }
                                                if (bank_name) {
                                                        header += 'Bank: ' + bank_name + '\n';
                                                }
                                                if (product_name) {
                                                        header += 'Product: ' + product_name + '\n';
                                                }
                                                if (status) {
                                                        header += 'Status: ' + status + '\n';
                                                }

                                                // If no filters are selected, return the CSV as is
                                                if (!header.trim()) {
                                                        return csv;
                                                }

                                                // Prepend the header with filters to the CSV content
                                                return header + '\n' + csv;
                                        }

                                },
                                {
                                        extend: 'excelHtml5',
                                        text: 'Excel',
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Application Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Application Details';
                                                } else if (product_name) {
                                                        return product_name + ' Application Details';
                                                } else {
                                                        return 'All Application Details';
                                                }
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
                                                var partner_name = $("#partner_name option:selected").html();
                                                var date = $("#date option:selected").html();
                                                if (date == "custom") {
                                                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                }
                                                if (date || date_range || partner_name || bank_name || product_name || status) {
                                                        if (date) header += 'Date: ' + date + '\n';
                                                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                                                        if (partner_name) header += 'Partner: ' + partner_name + '\n';
                                                        if (bank_name) header += 'Bank: ' + bank_name + '\n';
                                                        if (product_name) header += 'Product: ' + product_name + '\n';
                                                        if (status) header += 'Status: ' + status + '\n';
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
                                        title: 'Bank Application Details',
                                        exportOptions: {
                                                columns: function(index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Application Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Application Details';
                                                } else if (product_name) {
                                                        return product_name + ' Application Details';
                                                } else {
                                                        return 'All Application Details';
                                                }
                                        },
                                        customize: function(win) {
                                                var filters = '';
                                                var partner_name = $("#partner_name option:selected").html();
                                                var date = $("#date option:selected").html();
                                                if (date == "custom") {
                                                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                }
                                                if (date || date_range || partner_name || bank_name || product_name || status) {
                                                        filters += '<h4>Filters Applied:</h4>';
                                                        if (date) filters += '<p>Date: ' + date + '</p>';
                                                        if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                                                        if (partner_name) filters += '<p>Partner: ' + $("#partner_name option:selected").html() + '</p>';
                                                        if (bank_name) filters += '<p>Bank: ' + bank_name + '</p>';
                                                        if (product_name) filters += '<p>Product: ' + product_name + '</p>';
                                                        if (status) filters += '<p>Status: ' + status + '</p>';
                                                }

                                                $(win.document.body).prepend(filters);
                                        }
                                },
                        ],
                        processing: true,
                        serverSide: true,
                        ajax: {
                                url: "{{ route('application.index') }}",
                                data: {
                                        date: date,
                                        date_range: date_range,
                                        partner_name: partner_name,
                                        bank_name: bank_name,
                                        product_name: product_name,
                                        status: status,
                                },
                                error: function(xhr, error, thrown) {
                                        console.log(xhr.responseText);
                                }
                        },
                        columns: [{
                                        data: 'checkbox',
                                        orderable: false, // Disable sorting for the checkbox column
                                        searchable: false
                                }, // Checkbox column
                                {
                                        data: null,
                                        name: 'srno',
                                        render: function(data, type, row, meta) {
                                                return meta.row + 1 + meta.settings._iDisplayStart;
                                        },
                                        orderable: false,
                                        searchable: false
                                },
                                @if($user->roles[0]->id == 1) {
                                        data: 'user_id',
                                        name: 'user_id'
                                },
                                @endif {
                                        data: 'app_id',
                                        name: 'app_id'
                                },
                                {
                                        data: 'customer_name',
                                        name: 'customer_name'
                                },
                                {
                                        data: 'bank.name',
                                        name: 'bank.name'
                                },
                                {
                                        data: 'product.name',
                                        name: 'product.name'
                                },
                                {
                                        data: 'disburse_amount',
                                        name: 'disburse_amount'
                                },
                                {
                                        data: 'commission_rate',
                                        name: 'commission_rate'
                                },
                                {
                                        data: 'remark',
                                        name: 'remark'
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

                $(document).on('change', '.remark-dropdown', function() {
                        let remark = $(this).val();
                        let id = $(this).data('id');

                        $.ajax({
                                url: '{{ url("/application/update/remark") }}',
                                method: 'POST',
                                data: {
                                        _token: '{{ csrf_token() }}',
                                        id: id,
                                        remark: remark,
                                },
                                success: function(response) {
                                        if (response.success) {
                                                alert(response.message); // You can toast this or silently succeed
                                        }
                                },
                                error: function(xhr) {
                                        alert('Something went wrong.');
                                }
                        });
                });


                $('.select').select2({
                        placeholder: "Select an option",
                        allowClear: true
                });

                $('#filter').click(function() {
                        var date = $('#date').val();
                        var date_range = $('#date-range-picker').val();
                        var partner_name = $('#partner_name').val();
                        var bank_name = $('#bank_name').val();
                        var product_name = $('#product_name').val();
                        var status = $('#status').val();

                        if (date || partner_name || bank_name || product_name || status) {
                                $('.data-table').DataTable().destroy();
                                load_data(date, date_range, partner_name, bank_name, product_name, status);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });
</script>