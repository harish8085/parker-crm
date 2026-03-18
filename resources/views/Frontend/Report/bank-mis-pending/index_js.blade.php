<script>
    let bankMisPendingReportTable;

    function loadBankMisPendingReport() {
        if ($.fn.DataTable.isDataTable('#bankMisPendingReportTable')) {
            $('#bankMisPendingReportTable').DataTable().destroy();
        }

        bankMisPendingReportTable = $('#bankMisPendingReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report.bank-mis-pending.index') }}",
                data: {
                    user_id: $('#user_id').val(),
                    bank_id: $('#bank_id').val(),
                    product_id: $('#product_id').val(),
                    status: $('#status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'channel_partner_name', name: 'channel_partner_name', orderable: false },
                { data: 'bank_name', name: 'bank_name', orderable: false },
                { data: 'product_name', name: 'product_name', orderable: false }
            ]
        });
    }

    $(document).ready(function () {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        loadBankMisPendingReport();

        $('#filter').on('click', function () {
            loadBankMisPendingReport();
        });

        $('#refresh').on('click', function () {
            window.location.reload();
        });
    });
</script>
