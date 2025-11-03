<script>
        $(document).on('click', '.delete-sheet-btn', function () {
                if (confirm('Are you sure you want to delete this sheet?')) {
                        var bankId = $(this).data('sheet-id');
                        $.ajax({
                                url: '/sheet-matching/delete/' + bankId,
                                type: 'DELETE',
                                headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (response) {
                                        alert('Sheet deleted successfully.');
                                        // Reload the page or update the table to reflect the changes
                                        location.reload();
                                },
                                error: function (xhr) {
                                        console.log(xhr.responseText);
                                }
                        });
                }
        });

</script>
<script>
    $(document).ready(function() {
        $('form').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();

            // Serialize form data manually as JSON
            var formData = {
                from_date: $('#from').val(),
                to_date: $('#to').val(),
                status: $('#status').val()
            };

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: '/settlement/view/filter', // Replace with your server endpoint
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#tbody').html('')
                    $('#tbody').html(response)
                    $('#myModal').modal('hide')
                },
                error: function(xhr, status, error) {}
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

    function load_data(date = '', date_range = '', bank_name = '', product_name = '', group = '') {

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
                    title: 'Sheetdata for banks',
                    charset: 'UTF-8',
                    bom: true,
                    title: function() {
                        // Check if either bank_name or product_name is provided
                        return bank_name || product_name ? 'SheetData For: ' + (bank_name || '') + ' ' + (product_name || '') : 'SheetData For Banks';
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
                        if (date || date_range || group || bank_name || product_name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (bank_name) header += 'Bank: ' + bank_name + '\n';
                            if (product_name) header += 'Product: ' + product_name + '\n';
                            if (group) header += 'Group: ' + group + '\n';
                        }
                        return header + csv; // Prepend the filter information to the CSV content
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Sheetdata for banks',
                    title: function() {
                        // Check if either bank_name or product_name is provided
                        return bank_name || product_name ? 'SheetData For: ' + (bank_name || '') + ' ' + (product_name || '') : 'SheetData For Banks';
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
                        if (date || date_range || group || bank_name || product_name) {
                            if (date) header += 'Date: ' + date + '\n';
                            if (date_range) header += 'Date Range: ' + date_range + '\n';
                            if (bank_name) header += 'Bank: ' + bank_name + '\n';
                            if (product_name) header += 'Product: ' + product_name + '\n';
                            if (group) header += 'Group: ' + group + '\n';
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
                    title: 'Sheetdata for banks',
                    title: function() {
                        // Check if either bank_name or product_name is provided
                        return bank_name || product_name ? 'SheetData For: ' + (bank_name || '') + ' ' + (product_name || '') : 'SheetData For Banks';
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
                        if (date || date_range || group || bank_name || product_name) {
                            filters += '<h4>Filters Applied:</h4>';
                            if (date) filters += '<p>Date: ' + date + '</p>';
                            if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                            if (bank_name) filters += '<p>Bank: ' + bank_name + '</p>';
                            if (product_name) filters += '<p>Product: ' + product_name + '</p>';
                            if (group) filters += '<p>Group: ' + group + '</p>';
                        }

                        $(win.document.body).prepend(filters);
                    }
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sheet-matching.index') }}",
                data: {
                    date: date,
                    date_range: date_range,
                    bank_name: bank_name,
                    product_name: product_name,
                    group: group,
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
                    data: 'group',
                    name: 'group'
                },
                {
                    data: 'app_id',
                    name: 'app_id'
                },
                {
                    data: 'case_location',
                    name: 'case_location'
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
                    data: 'disbAmount',
                    name: 'disbAmount'
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
                    data: 'payout_amount',
                    name: 'payout_amount'
                },
                {
                    data: 'payout_rate',
                    name: 'payout_rate'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'month',
                    name: 'month'
                },
                {
                    data: 'pf_per',
                    name: 'pf_per'
                },
                {
                    data: 'kli',
                    name: 'kli'
                },
                {
                    data: 'kli_payout_per',
                    name: 'kli_payout_per'
                },
                {
                    data: 'kli_payout',
                    name: 'kli_payout'
                },
                {
                    data: 'kgi',
                    name: 'kgi'
                },
                {
                    data: 'kgi_payout_per',
                    name: 'kgi_payout_per'
                },
                {
                    data: 'kgi_payout',
                    name: 'kgi_payout'
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
            var group = $('#group').val();

            if (date || bank_name || product_name || group) {
                $('.data-table').DataTable().destroy();
                load_data(date, date_range, bank_name, product_name, group);
            } else {
                alert('Select at least one filter!');
            }
        });

        $('#refresh').click(function() {
            window.location.reload();
        });
    });
</script>