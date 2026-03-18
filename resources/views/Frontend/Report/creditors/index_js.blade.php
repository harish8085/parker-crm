<script>
    let creditorsReportTable;

    function loadCreditorsReport() {
        if ($.fn.DataTable.isDataTable('#creditorsReportTable')) {
            $('#creditorsReportTable').DataTable().destroy();
        }

        creditorsReportTable = $('#creditorsReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report.creditors.index') }}",
                data: {
                    user_id: $('#user_id').val(),
                    settlement_type: $('#settlement_type').val(),
                    payment_status: $('#payment_status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'parent_name', name: 'parent_name', orderable: false, searchable: false },
                { data: 'channel_name', name: 'channel_name', orderable: false, searchable: false },
                { data: 'payable_amt', name: 'payable_amt', orderable: false, searchable: false },
                { data: 'paid_amt', name: 'paid_amt', orderable: false, searchable: false },
                { data: 'remaining_amt', name: 'remaining_amt', orderable: false, searchable: false },
                { data: 'tds_amt', name: 'tds_amt', orderable: false, searchable: false },
                { data: 'net_remaining_amt', name: 'net_remaining_amt', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        loadCreditorsReport();

        $('#filter').on('click', function () {
            loadCreditorsReport();
        });

        $('#refresh').on('click', function () {
            window.location.reload();
        });
    });
</script>
