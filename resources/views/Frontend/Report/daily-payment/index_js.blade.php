<script>
    let dailyPaymentReportTable;

    function loadDailyPaymentReport() {
        if ($.fn.DataTable.isDataTable('#dailyPaymentReportTable')) {
            $('#dailyPaymentReportTable').DataTable().destroy();
        }

        dailyPaymentReportTable = $('#dailyPaymentReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report.daily-payment.index') }}",
                data: {
                    user_id: $('#user_id').val(),
                    settlement_type: $('#settlement_type').val(),
                    status: $('#status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'date_display', name: 'date_display', orderable: false, searchable: false },
                { data: 'type_display', name: 'type_display', orderable: false, searchable: false },
                { data: 'status_display', name: 'status_display', orderable: false, searchable: false },
                { data: 'referral_name', name: 'referral_name', orderable: false, searchable: false },
                { data: 'amount_display', name: 'amount_display', orderable: false, searchable: false },
                { data: 'remark_display', name: 'remark_display', orderable: false, searchable: false },
                { data: 'account_holder_name', name: 'account_holder_name', orderable: false, searchable: false },
                { data: 'account_number', name: 'account_number', orderable: false, searchable: false },
                { data: 'ifsc_code', name: 'ifsc_code', orderable: false, searchable: false },
                { data: 'pan_number', name: 'pan_number', orderable: false, searchable: false },
                { data: 'aadhar_number', name: 'aadhar_number', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        loadDailyPaymentReport();

        $('#filter').on('click', function () {
            loadDailyPaymentReport();
        });

        $('#refresh').on('click', function () {
            window.location.reload();
        });
    });
</script>
