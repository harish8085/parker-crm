<script>
$(document).ready(function() {
    // Initialize DataTable
    load_data();

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
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    // Show or hide the date range picker based on the selected option
    $('#date').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.date_range').show();
        } else {
            $('.date_range').hide();
        }
    });

    // Master checkbox functionality for bulk selection
    $(document).on('change', '#masterCheckbox', function() {
        const isChecked = $(this).prop('checked');
        $('.rowCheckbox').prop('checked', isChecked);
        updateButtonVisibility();
    });

    // Filter button
    $('#filter').click(function() {
        const date = $('#date').val();
        const dateRange = $('#date-range-picker').val();
        const bankName = $('#bank_name').val();
        
        load_data(date, dateRange, bankName);
    });

    // Refresh button
    $('#refresh').click(function() {
        window.location.reload();
    });

    $('.select').select2({
        allowClear: true
    });
});

// DataTable initialization function
$.fn.dataTable.ext.errMode = 'none';

function load_data(date = '', dateRange = '', bankName = '') {
    if ($.fn.dataTable.isDataTable('#bankMisTable')) {
        $('#bankMisTable').DataTable().destroy();
    }

    var table = $('#bankMisTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("invoice_payment.index") }}',
            type: 'GET',
            data: function(d) {
                d.date = date;
                d.date_range = dateRange;
                d.bank_name = bankName;
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable Error:', error, thrown);
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'bank_name', name: 'bank_name' },
            { data: 'bank_address', name: 'bank_address' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'bank_gst_no', name: 'bank_gst_no' },
            { data: 'bank_hsn_code', name: 'bank_hsn_code' },
            { data: 'dsa_pan', name: 'dsa_pan' },
            { data: 'dsa_gst_no', name: 'dsa_gst_no' },
            { data: 'CGST', name: 'CGST' },
            { data: 'SGST', name: 'SGST' },
            { data: 'IGST', name: 'IGST' },
            { data: 'payment_recevied_bank', name: 'payment_recevied_bank' },
            { data: 'taxable_value', name: 'taxable_value' },
            { data: 'invoive_value', name: 'invoive_value' },
            { data: 'payment_amount', name: 'payment_amount' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'remaining_amount', name: 'remaining_amount' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        dom: 'rtip'
    });

    return table;
}
</script>