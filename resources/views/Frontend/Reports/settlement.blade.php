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
        <h3 class="application-heading">Settlement Report</h3>
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
            'action' => route('reports.settlement'),
            'exportType' => 'settlement',
            'statuses' => ['pending', 'completed']
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">Settlement ID</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">Distributions</th>
                    <th class="table-header">Gross Amount</th>
                    <th class="table-header">Amount</th>
                    <th class="table-header">Status</th>
                    <th class="table-header">Settlement Date</th>
                    <th class="table-header">Created Date</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalGross" style="font-weight:700;"></th>
                    <th id="totalAmount" style="font-weight:700;"></th>
                    <th colspan="3"></th>
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
                { extend: 'csvHtml5', text: 'CSV', title: 'Settlement Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Settlement Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Settlement Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.settlement') }}",
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
                { data: 'settlement_id', name: 'settlements.id' },
                { data: 'user_name', name: 'users.first_name' },
                { data: 'distributions_count', name: 'distributions_count', searchable: false },
                { data: 'gross_amount', name: 'settlements.gross_amount' },
                { data: 'amount', name: 'settlements.amount' },
                { data: 'status', name: 'settlements.status' },
                { data: 'settlement_date', name: 'settlements.settlement_date' },
                { data: 'created_at', name: 'settlements.created_at' }
            ],
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalGross').html(formatIndianNumber(json.totals.gross_amount));
                    $('#totalAmount').html(formatIndianNumber(json.totals.amount));
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
