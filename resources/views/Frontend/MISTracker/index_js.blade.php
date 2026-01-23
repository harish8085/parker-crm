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


    // Filter button
    $('#filter').click(function() {
        const date = $('#date').val();
        const dateRange = $('#date-range-picker').val();
        const bankName = $('#bank_name').val();

        load_data(date, dateRange, bankName, paymentStatus);
    });

    // Refresh button
    $('#refresh').click(function() {
        window.location.reload();
    });
</script>