<script>
$(document).ready(function() {
    // Delete button functionality
    $(document).on('click', '.delete-btn', function() {
        var bankId = $(this).data('bank-id');
        if (confirm('Are you sure you want to delete this bank mis?')) {
            $.ajax({
                url: '/invoice/delete/' + bankId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Invoice deleted successfully.');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.log('Error:', xhr.responseText);
                    alert('An error occurred while deleting the invoice.');
                }
            });
        }
    });

    // Show/hide the action button based on selected checkboxes
    const $actionButton = $('#actionButton');
    
    function updateButtonVisibility() {
        const selectedCount = $('.rowCheckbox:checked').length;
        if (selectedCount > 0) {
            $actionButton.show();
        } else {
            $actionButton.hide();
        }
    }

    // Attach event handlers to row checkboxes using delegation
    $(document).on('change', '.rowCheckbox', function() {
        updateButtonVisibility();
    });

    // Initialize with button hidden
    $actionButton.hide();

    // Date range picker initialization
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
            $('.date_range').show();
        } else {
            $('.date_range').hide();
        }
    });

    // Master checkbox functionality for bulk selection
    $(document).on('change', '#masterCheckbox', function() {
        const isChecked = this.checked;
        $('.rowCheckbox').prop('checked', isChecked);
        updateButtonVisibility();
    });
});

// Function to confirm invoice generation
function confirmInvoiceGeneration(selectedIds) {
    // Add your logic here to actually generate the invoice
    alert('Invoice generation confirmed!');
    $('#invoiceModal').modal('hide');
}
</script>

<!-- DataTable -->
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
        buttons: [
            {
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
            url: "{{ route('invoice.index') }}",
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
            {
                data: 'checkbox',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' + row.id + '">';
                }
            },
            {
                data: null,
                name: 'srno',
                render: function(data, type, row, meta) {
                    return meta.row + 1 + meta.settings._iDisplayStart;
                },
                orderable: false,
                searchable: false
            },
            { data: 'bank_id', name: 'bank_id' },
            { data: 'product_id', name: 'product_id' },
            { data: 'group', name: 'group' },
            { data: 'app_id', name: 'app_id' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'customer_firm_name', name: 'customer_firm_name' },
            { data: 'location', name: 'location' },
            { data: 'case_location', name: 'case_location' },
            { data: 'disbAmount', name: 'disbAmount' },
            { data: 'payout_amount', name: 'payout_amount' },
            { data: 'payout_rate', name: 'payout_rate' },
            { data: 'pf', name: 'pf' },
            { data: 'subvention', name: 'subvention' },
            { data: 'roi', name: 'roi' },
            { data: 'insurance', name: 'insurance' },
            { data: 'otc_pdd_status', name: 'otc_pdd_status' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
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