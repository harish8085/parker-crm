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
        <h3 class="application-heading">TDS Report</h3>
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
            'action' => route('reports.tds'),
            'exportType' => 'tds'
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">Application ID</th>
                    <th class="table-header">Disburse Amount</th>
                    @if($isAdmin)
                    <th class="table-header">Commission Rate</th>
                    <th class="table-header">Company Commission</th>
                    @endif
                    <th class="table-header">{{ $isAdmin ? 'Channel Share' : 'Gross Amount' }}</th>
                    <th class="table-header">TDS %</th>
                    <th class="table-header">TDS Amount</th>
                    <th class="table-header">Net Amount</th>
                    @if($isAdmin)
                    <th class="table-header">Company Net</th>
                    @endif
                    <th class="table-header">Date</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    @if($isAdmin)
                    <th colspan="5" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalCompanyComm" style="font-weight:700;"></th>
                    @else
                    <th colspan="4" class="text-end" style="font-weight:700;">Totals:</th>
                    @endif
                    <th id="totalGross" style="font-weight:700;"></th>
                    <th></th>
                    <th id="totalTds" style="font-weight:700;"></th>
                    <th id="totalNet" style="font-weight:700;"></th>
                    @if($isAdmin)
                    <th id="totalCompanyNet" style="font-weight:700;"></th>
                    @endif
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
    var isAdmin = @json($isAdmin);

    function load_data(date_from, date_to, user_id) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_name', name: 'users.first_name' },
            { data: 'app_id', name: 'applications.app_id' },
            { data: 'disburse_amount', name: 'applications.disburse_amount' }
        ];

        if (isAdmin) {
            columns.push({ data: 'commission_rate_display', name: 'applications.commission_rate' });
            columns.push({ data: 'company_commission', name: 'company_commission', orderable: false, searchable: false });
        }

        columns.push({ data: 'gross_amount', name: 'settlement_distributions.gross_amount' });
        columns.push({ data: 'tds_percentage_display', name: 'settlement_distributions.tds_percentage' });
        columns.push({ data: 'tds', name: 'settlement_distributions.tds' });
        columns.push({ data: 'amount', name: 'settlement_distributions.amount' });

        if (isAdmin) {
            columns.push({ data: 'company_net', name: 'company_net', orderable: false, searchable: false });
        }

        columns.push({ data: 'created_at', name: 'settlement_distributions.created_at' });

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'TDS Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'TDS Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'TDS Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.tds') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.user_id = user_id || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns,
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalGross').html(formatIndianNumber(json.totals.gross_amount));
                    $('#totalTds').html(formatIndianNumber(json.totals.tds));
                    $('#totalNet').html(formatIndianNumber(json.totals.net_amount));
                    if (isAdmin) {
                        $('#totalCompanyComm').html(formatIndianNumber(json.totals.company_commission));
                        $('#totalCompanyNet').html(formatIndianNumber(json.totals.company_net));
                    }
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
