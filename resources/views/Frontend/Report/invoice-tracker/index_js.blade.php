<script>
    let invoiceTrackerReportTable;

    function loadInvoiceTrackerReport() {
        if ($.fn.DataTable.isDataTable('#invoiceTrackerReportTable')) {
            $('#invoiceTrackerReportTable').DataTable().destroy();
        }

        invoiceTrackerReportTable = $('#invoiceTrackerReportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report.invoice-tracker.index') }}",
                data: {
                    bank_name: $('#bank_name').val(),
                    payment_status: $('#payment_status').val(),
                    mis_month: $('#mis_month').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'mis_month', name: 'mis_month' },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'payment_status_badge', name: 'payment_status_badge', orderable: false, searchable: false },
                { data: 'payment_amount', name: 'payment_amount' },
                { data: 'remaining_amount', name: 'remaining_amount' },
                { data: 'invoice_date_display', name: 'invoice_date_display', orderable: false, searchable: false },
                { data: 'application_nos', name: 'application_nos', orderable: false, searchable: false }
            ]
        });
    }

    $(document).ready(function () {
        loadInvoiceTrackerReport();

        $('#filter').on('click', function () {
            loadInvoiceTrackerReport();
        });

        $('#refresh').on('click', function () {
            window.location.reload();
        });
    });
</script>
