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

    // Show or hide the date range picker based on the selected option
    $('#date').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.date_range').show();
        } else {
            $('.date_range').hide();
        }
    });

    // Function to handle filtering and pagination
    function filterAndPaginate(page = 1) {
        var formData = $('#filterForm').serialize() + '&page=' + page;

        // Send AJAX request to fetch filtered data
        $.ajax({
            type: 'GET',
            url: '/bank_mis/view/filter',
            data: formData,
            success: function(response) {
                $('#myTable').html(response);
                $('#myModal').modal('hide');
                // Bind click event to pagination links after new content is loaded
                $('.pagination a').click(function(e) {
                    e.preventDefault();
                    var page = $(this).attr('href').split('page=')[1];
                    filterAndPaginate(page);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }



    // Filter button
    $('#filter').click(function() {
        const date = $('#date').val();
        const dateRange = $('#date-range-picker').val();
        const bankName = $('#bank').val();

        load_data(date, dateRange, bank, product);
    });

    // Refresh button
    $('#refresh').click(function() {
        window.location.reload();
    });

    // Master checkbox functionality for bulk selection
    $(document).on('change', '#masterCheckbox', function() {
        const isChecked = this.checked;
        $('.rowCheckbox').prop('checked', isChecked);
        updateButtonVisibility();
    });
</script>

<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';

    function load_data(date = '', date_range = '', bank_name = '', product_name = '') {
        var table = $('.data-table').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [{
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Invoice-Details'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Invoice-Details'
                },
                {
                    extend: 'print',
                    text: 'Print'
                }
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('mis_tracker.index') }}",
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
            columns: [
                // {
                //     data: 'checkbox',
                //     orderable: false,
                //     searchable: false,
                //     render: function(data, type, row) {
                //         return '<input type="checkbox" class="rowCheckbox" value="' + row.id + '">';
                //     }
                // },
                {
                    data: null,
                    name: 'srno',
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'bank_mis_month',
                    name: 'bank_mis_month'
                },
                {
                    data: 'bank',
                    name: 'bank'
                },
                {
                    data: 'product',
                    name: 'product'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: false,
                //     searchable: false
                // }
            ]
        });
    }

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