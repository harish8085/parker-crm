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
        <h3 class="application-heading">Advance Report</h3>
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
            'action' => route('reports.advance'),
            'exportType' => 'advance'
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">Current Balance</th>
                    <th class="table-header">Log Date</th>
                    <th class="table-header">Type</th>
                    <th class="table-header">Amount</th>
                    <th class="table-header">Remark</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalAdded" style="font-weight:700;"></th>
                    <th id="totalDeducted" style="font-weight:700;"></th>
                    <th></th>
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

    function load_data(date_from, date_to, user_id) {
        table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Advance Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Advance Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Advance Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.advance') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.user_id = user_id || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user_name', name: 'users.first_name' },
                { data: 'current_balance', name: 'advances.advance_amount' },
                { data: 'created_at', name: 'advance_amount_logs.created_at' },
                { data: 'type', name: 'advance_amount_logs.type' },
                { data: 'advance_amount', name: 'advance_amount_logs.advance_amount' },
                { data: 'remark', name: 'advance_amount_logs.remark' }
            ],
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalAdded').html('<span class="badge bg-success">Added: ' + formatIndianNumber(json.totals.total_added) + '</span>');
                    $('#totalDeducted').html('<span class="badge bg-danger">Deducted: ' + formatIndianNumber(json.totals.total_deducted) + '</span>');
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

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to, user_id);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('#reportUserSelect').val(null).trigger('change');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
