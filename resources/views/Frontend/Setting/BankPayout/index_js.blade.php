<script>
        $(document).ready(function() {
                // Set CSRF token globally for AJAX requests
                $.ajaxSetup({
                        headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });

                // Event delegation for dynamic elements
                $(document).on('click', '.delete-payout-btn', function() {
                        if (confirm('Are you sure you want to delete this payout?')) {
                                performAjaxRequest('/bank-payout/delete/' + $(this).data('payout-id'), 'DELETE', {}, function() {
                                        location.reload();
                                });
                        }
                });

                function performAjaxRequest(url, type, data, successCallback) {
                        $.ajax({
                                url: url,
                                type: type,
                                data: data,
                                success: successCallback,
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
        function load_data(date = '', date_range = '', bank_name = '', product_name = '') {

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
                                        title: 'Bank Payout Details',
                                        bom: true,
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank Code Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank Code Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank Code Details';
                                                } else {
                                                        return 'All Bank Code Details';
                                                }
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
                                                if(date == "custom"){
                                                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                }
                                                if (date || date_range || bank_name || product_name) {
                                                        if (date) header += 'Date: ' + date + '\n';
                                                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                                                        if (bank_name) header += 'Bank: ' + bank_name + '\n';
                                                        if (product_name) header += 'Product: ' + product_name + '\n';
                                                }
                                                return header + csv; // Prepend the filter information to the CSV content
                                        }
                                },
                                {
                                        extend: 'excelHtml5',
                                        text: 'Excel',
                                        title: 'Bank Payout Details',
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank Code Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank Code Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank Code Details';
                                                } else {
                                                        return 'All Bank Code Details';
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
                                                var date = $("#date option:selected").html();
                                                if(date == "custom"){
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
                                        title: 'Bank Payout Details',
                                        title: function() {
                                                if (bank_name && product_name) {
                                                        return bank_name + ' - ' + product_name + ' Bank Code Details';
                                                } else if (bank_name) {
                                                        return bank_name + ' Bank Code Details';
                                                } else if (product_name) {
                                                        return product_name + ' Bank Code Details';
                                                } else {
                                                        return 'All Bank Code Details';
                                                }
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
                                                if(date == "custom"){
                                                        var date_range = $("#date-range-picker").val(); // Adjust according to your HTML structure
                                                }
                                                if (date || date_range || bank_name || product_name) {
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
                                url: "{{ route('bank-payout.index') }}",
                                data: {
                                        date: date,
                                        date_range: date_range,
                                        bank_name: bank_name,
                                        product_name: product_name,
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
                                        data: 'bank.name',
                                        name: 'bank.name'
                                },
                                {
                                        data: 'product.name',
                                        name: 'product.name'
                                },
                                {
                                        data: 'rate',
                                        name: 'rate'
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
                        var bank_name = $('#bank_name').val();
                        var product_name = $('#product_name').val();

                        if (date || bank_name || product_name) {
                                $('.data-table').DataTable().destroy();
                                load_data(date, date_range, bank_name, product_name);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });
</script>