<script>
    let advanceReportTable;

    function loadAdvanceReport() {
        if ($.fn.DataTable.isDataTable('#advanceReportTable')) {
            $('#advanceReportTable').DataTable().destroy();
        }

        advanceReportTable = $('#advanceReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report.advance.index') }}",
                data: {
                    user_id: $('#user_id').val(),
                    advance_type: $('#advance_type').val(),
                    min_amount: $('#min_amount').val(),
                    max_amount: $('#max_amount').val(),
                    company: $('#company').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'advance_type', name: 'advance_type', orderable: false, searchable: false },
                { data: 'channel_name', name: 'channel_name', orderable: false, searchable: false },
                { data: 'advance_paid_to', name: 'advance_paid_to', orderable: false, searchable: false },
                { data: 'advance_amt', name: 'advance_amt', orderable: false, searchable: false },
                { data: 'payment_date', name: 'payment_date', orderable: false, searchable: false },
                { data: 'company_label', name: 'company_label', orderable: false, searchable: false },
                { data: 'recovery_date', name: 'recovery_date', orderable: false, searchable: false },
                { data: 'recovery_amt', name: 'recovery_amt', orderable: false, searchable: false },
                { data: 'net_advance', name: 'net_advance', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        loadAdvanceReport();

        $('#filter').on('click', function () {
            loadAdvanceReport();
        });

        $('#refresh').on('click', function () {
            window.location.reload();
        });
    });
</script>
