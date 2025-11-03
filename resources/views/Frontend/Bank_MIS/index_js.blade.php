<script>
        $(document).ready(function () {
                // Delete button functionality
                $(document).on('click', '.delete-btn', function () {
                        var bankId = $(this).data('bank-id'); // Get the bank ID from the data attribute

                        // Confirm deletion with the user
                        if (confirm('Are you sure you want to delete this bank mis?')) {
                                $.ajax({
                                        url: '/bank_mis/delete/' + bankId,
                                        type: 'DELETE',
                                        headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token for security
                                        },
                                        success: function (response) {
                                                // Provide feedback to the user
                                                alert('Bank MIS deleted successfully.');

                                                // Remove the row corresponding to the deleted bank MIS
                                                location.reload();
                                        },
                                        error: function (xhr, status, error) {
                                                // Log error message for debugging
                                                console.log('Error:', xhr.responseText);
                                                alert('An error occurred while deleting the bank MIS. Please try again.');
                                        }
                                });
                        }
                });

                // Show/hide the action button based on selected checkboxes
                const $actionButton = $('#actionButton'); // Reference to the button to show/hide
                function updateButtonVisibility() {
                        const selectedCount = $('.rowCheckbox:checked').length;
                        if (selectedCount > 0) {
                                $actionButton.show(); // Show button if exactly one checkbox is selected
                        } else {
                                $actionButton.hide(); // Hide button otherwise
                        }
                }

                // Attach event handlers to row checkboxes
                $('#bankMisTable tbody').on('change', '.rowCheckbox', function () {
                        updateButtonVisibility();
                });

                // Initialize with button hidden
                $actionButton.hide();

                // Function to handle filtering and pagination
                function filterAndPaginate(page = 1) {
                        var formData = $('#filterForm').serialize() + '&page=' + page;

                        // Send AJAX request to fetch filtered data
                        $.ajax({
                                type: 'GET',
                                url: '/bank_mis/view/filter',
                                data: formData,
                                success: function (response) {
                                        $('#myTable').html(response);
                                        $('#myModal').modal('hide');
                                        // Bind click event to pagination links after new content is loaded
                                        $('.pagination a').click(function (e) {
                                                e.preventDefault();
                                                var page = $(this).attr('href').split('page=')[1];
                                                filterAndPaginate(page);
                                        });
                                },
                                error: function (xhr, status, error) {
                                        console.error(error);
                                }
                        });
                }

                // Attach event handler to pagination links
                $('.pagination a').click(function (e) {
                        e.preventDefault();
                        var page = $(this).attr('href').split('page=')[1];
                        filterAndPaginate(page);
                });

                // Event handler for form submission
                $('#filterForm').submit(function (event) {
                        event.preventDefault();
                        filterAndPaginate();
                });

                // Initial setup for hiding/showing elements based on user type
                $('.sales').hide();
                $('.channel').hide();
                $('#user_type').change(function () {
                        if ($('#user_type').val() == 'channel') {
                                $('.channel').show();
                                $('.sales').hide();
                        } else {
                                $('.sales').show();
                                $('.channel').hide();
                        }
                });

                // Date range picker initialization
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

                // Master checkbox functionality for bulk selection
                $('#masterCheckbox').on('change', function () {
                        const isChecked = this.checked;
                        $('.rowCheckbox').prop('checked', isChecked);
                        $actionButton.show();
                });

                // Update master checkbox based on row checkboxes
                $('#applicationsTable tbody').on('change', '.rowCheckbox', function () {
                        const allChecked = $('.rowCheckbox').length === $('.rowCheckbox:checked').length;
                        $('#masterCheckbox').prop('checked', allChecked);
                        if ($('.rowCheckbox:checked').length == 0) {
                                $actionButton.hide();
                        } else {
                                $actionButton.show();
                        }
                });

                // Bulk delete functionality
                $('#bulkDeleteBtn').on('click', function () {
                        const selectedIds = $('.rowCheckbox:checked')
                                .map(function () {
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
                                url: "{{ route('bank-mis.bulk-delete') }}",
                                type: "POST",
                                data: {
                                        mis_ids: selectedIds,
                                        _token: "{{ csrf_token() }}",
                                },
                                success: function (response) {
                                        alert(response.message);
                                        location.reload() // Reload DataTable to reflect changes
                                },
                                error: function (xhr) {
                                        alert(xhr.responseJSON.message || 'An error occurred.');
                                },
                        });
                });

        });
</script>

<!-- Datatable -->
<script type="text/javascript">
        $.fn.dataTable.ext.errMode = 'none';
        function load_data(date = '', date_range = '', bank_name = '', product_name = '') {

                var table = $('.data-table').DataTable({
                        debug: false, // Disable debugging
                        dom: 'Bfrtip<"bottom"l>',
                        lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']], // Options for the "Show entries" dropdown
                        buttons: [
                                {
                                        extend: 'csvHtml5',
                                        text: 'CSV',
                                        title: 'Bank-MIS',
                                        charset: 'UTF-8',
                                        bom: true,
                                        title: function () {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank MIS Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank MIS Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank MIS Details';
                                                } else {
                                                        return 'All Bank MIS Details';
                                                }
                                        },
                                        exportOptions: {
                                                columns: function (index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        customize: function (csv) {
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
                                                if (bank_name) {
                                                        header += 'Bank: ' + bank_name + '\n';
                                                }
                                                if (product_name) {
                                                        header += 'Product: ' + product_name + '\n';
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
                                        title: function () {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank MIS Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank MIS Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank MIS Details';
                                                } else {
                                                        return 'All Bank MIS Details';
                                                }
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
                                                if (date || date_range || bank_name || product_name) {
                                                        if (date) header += 'Date: ' + date + '\n';
                                                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                                                        if (bank_name) header += 'Bank: ' + bank_name + '\n';
                                                        if (product_name) header += 'Product: ' + product_name + '\n';

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
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank MIS Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank MIS Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank MIS Details';
                                                } else {
                                                        return 'All Bank MIS Details';
                                                }
                                        },
                                        exportOptions: {
                                                columns: function (index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        customize: function (win) {
                                                var filters = '';
                                                if (date || date_range || bank_name || product_name) {
                                                        var date = $("#date option:selected").html();
                                                        if (date == "custom") {
                                                                var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                        }
                                                        filters += '<h4>Filters Applied:</h4>';
                                                        if (date) filters += '<p>Date: ' + date + '</p>';
                                                        if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                                                        if (bank_name) filters += '<p>Bank: ' + bank_name + '</p>';
                                                        if (product_name) filters += '<p>Product: ' + product_name + '</p>';
                                                }

                                                $(win.document.body).prepend(filters);
                                        }
                                },
                        ],
                        processing: true,
                        serverSide: true,
                        ajax: {
                                url: "{{ route('bankmis.index') }}",
                                data: {
                                        date: date,
                                        date_range: date_range,
                                        bank_name: bank_name,
                                        product_name: product_name,
                                },
                                error: function (xhr, error, thrown) {
                                        console.log(xhr.responseText);
                                },
                        },
                        columns: [{
                                data: 'checkbox',
                                orderable: false, // Disable sorting for the checkbox column
                                searchable: false
                        }, {
                                data: null,
                                name: 'srno',
                                render: function (data, type, row, meta) {
                                        return meta.row + 1 + meta.settings._iDisplayStart;
                                },
                                orderable: false,
                                searchable: false
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
                                data: 'group',
                                name: 'group'
                        },
                        {
                                data: 'app_id',
                                name: 'app_id'
                        },
                        {
                                data: 'customer_name',
                                name: 'customer_name'
                        },
                        {
                                data: 'customer_firm_name',
                                name: 'customer_firm_name'
                        },
                        {
                                data: 'location',
                                name: 'location'
                        },
                        {
                                data: 'case_location',
                                name: 'case_location'
                        },
                        {
                                data: 'disbAmount',
                                name: 'disbAmount'
                        },
                        {
                                data: 'payout_amount',
                                name: 'payout_amount'
                        },
                        {
                                data: 'payout_rate',
                                name: 'payout_rate'
                        },
                        {
                                data: 'pf',
                                name: 'pf'
                        },
                        {
                                data: 'subvention',
                                name: 'subvention'
                        },
                        {
                                data: 'roi',
                                name: 'roi'
                        },
                        {
                                data: 'insurance',
                                name: 'insurance'
                        },
                        {
                                data: 'otc_pdd_status',
                                name: 'otc_pdd_status'
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
                        var product_name = $('#product_name').val();

                        if (date || bank_name || product_name) {
                                $('.data-table').DataTable().destroy();
                                load_data(date, date_range, bank_name, product_name);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function () {
                        window.location.reload();
                });
        });
</script>