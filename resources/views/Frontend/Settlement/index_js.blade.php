
<!-- Datatable -->
<script type="text/javascript">
                $.fn.dataTable.ext.errMode = 'none';

       function load_data(partner_name = '') {
                var table = $('.data-table').DataTable({
                        debug: false, // Disable debugging
                        dom: 'Bfrtip<"bottom"l>', // 'l' adds the "Show entries" dropdown
                        lengthMenu: [
                                [10, 25, 50, 100, 500, -1],
                                [10, 25, 50, 100, 500, 'All']
                        ], // Options for the "Show entries" dropdown
                        buttons: [
                                {
                                        extend: 'csvHtml5',
                                        text: 'CSV',
                                        title: 'Settlement Details',
                                        charset: 'UTF-8',
                                        bom: true,
                                        title: function() {
                                                return partner_name ? partner_name + ' Settlement Details ' : 'All Settlement Details';
                                        },
                                        exportOptions: {
                                                columns: function(index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        customize: function(csv) {
                                                var header = ''; // Initialize empty header

                                                // Fetch selected options and values
                                                var partner_name = $("#partner_name option:selected").html();

                                                // Conditionally append filters to the header

                                                if (partner_name) {
                                                        header += 'Partner: ' + partner_name + '\n';
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
                                                return partner_name ? partner_name + ' Settlement Details ' : 'All Settlements Details';
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
                                                if (partner_name) {
                                                        if (partner_name) header += 'Partner: ' + partner_name + '\n';
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
                                                return partner_name ? partner_name + ' Settlement Details ' : 'All Settlement Details';
                                        },
                                        exportOptions: {
                                                columns: function(index, data, node) {
                                                        // Exclude the action column (assuming it's the last column)
                                                        return index !== table.column(':last').index();
                                                }
                                        },
                                        customize: function(win) {
                                                var filters = '';
                                                var partner_name = $("#partner_name option:selected").html();
                                                if (partner_name) {
                                                        filters += '<h4>Filters Applied:</h4>';
                                                        if (partner_name) filters += '<p>Partner Name: ' + partner_name + '</p>';
                                                }

                                                $(win.document.body).prepend(filters);
                                        }
                                },
                        ],
                        processing: true,
                        serverSide: true,
                        ajax: {
                                url: "{{ route('settlement.index') }}",
                                data: {
                                        partner_name: partner_name,
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
                                        data: 'first_name',
                                        name: 'first_name'
                                },
                                {
                                        data: 'net_amount',
                                        name: 'net_amount'
                                },
                                {
                                        data: 'tds_amount',
                                        name: 'tds_amount'
                                },
                                {
                                        data: 'payout_amount',
                                        name: 'payout_amount'
                                },
                              
                                {
                                        data: 'paid_amount',
                                        name: 'paid_amount'
                                },
                                {
                                        data: 'remaining_amount',
                                        name: 'remaining_amount'
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
                        var partner_name = $('#partner_name').val();
                        if (partner_name) {
                                $('.data-table').DataTable().destroy();
                                load_data(partner_name);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });
</script>