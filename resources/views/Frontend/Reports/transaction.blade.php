@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/commen.css') }}">
@endsection
@section('body')
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Transaction Report</h3>
        <div class="btn-container">
            <a href="{{ route('reports.index') }}" style="text-decoration: none;">
                <button class="application-header-btn">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
            </a>
        </div>
    </div>

    <div class="p-4 pt-0">
        @include('Frontend.Reports._filters', [
            'action' => route('reports.transaction'),
            'exportType' => 'transaction',
            'statuses' => ['pending', 'approved', 'completed', 'rejected', 'cancelled']
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">Transaction ID</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">Gross Amount</th>
                    <th class="table-header">TDS Amount</th>
                    <th class="table-header">Advance Deduction</th>
                    <th class="table-header">Net Payable</th>
                    <th class="table-header">Status</th>
                    <th class="table-header">Date</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalGross" style="font-weight:700;"></th>
                    <th id="totalTds" style="font-weight:700;"></th>
                    <th id="totalAdvance" style="font-weight:700;"></th>
                    <th id="totalNet" style="font-weight:700;"></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    var table;

    function load_data(date_from, date_to, user_id, status) {
        table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Transaction Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Transaction Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Transaction Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.transaction') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.user_id = user_id || '';
                    d.status = status || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'transaction_id', name: 'transactions.transaction_id' },
                { data: 'user_name', name: 'users.first_name' },
                { data: 'gross_amount', name: 'transactions.gross_amount' },
                { data: 'tds_amount', name: 'transactions.tds_amount' },
                { data: 'advance_amount', name: 'transactions.advance_amount' },
                { data: 'net_payable', name: 'transactions.net_payable' },
                { data: 'status', name: 'transactions.status' },
                { data: 'created_at', name: 'transactions.created_at' }
            ],
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalGross').html(formatIndianNumber(json.totals.gross_amount));
                    $('#totalTds').html(formatIndianNumber(json.totals.tds_amount));
                    $('#totalAdvance').html(formatIndianNumber(json.totals.advance_amount));
                    $('#totalNet').html(formatIndianNumber(json.totals.net_payable));
                }
            }
        });
    }

    function formatIndianNumber(num) {
        if (!num && num !== 0) return '-';
        num = parseFloat(num);
        var parts = num.toFixed(2).split('.');
        var intPart = parts[0];
        var decPart = parts[1];
        var lastThree = intPart.slice(-3);
        var otherNumbers = intPart.slice(0, -3);
        if (otherNumbers !== '') lastThree = ',' + lastThree;
        return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree + '.' + decPart;
    }

    $(document).ready(function() {
        load_data();

        $('#reportFilterBtn').click(function() {
            var date_from = $('#reportDateFrom').val();
            var date_to = $('#reportDateTo').val();
            var user_id = $('#reportUserSelect').val();
            var status = $('#reportStatus').val();

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to, user_id, status);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('#reportUserSelect').val(null).trigger('change');
            $('#reportStatus').val('');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
