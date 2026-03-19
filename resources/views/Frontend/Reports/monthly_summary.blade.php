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
        <h3 class="application-heading">Monthly Summary Report</h3>
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
            'action' => route('reports.monthly-summary'),
            'exportType' => 'monthly-summary',
            'hideUserFilter' => true
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">Month</th>
                    <th class="table-header">Total Applications</th>
                    <th class="table-header">Total Disbursement</th>
                    @if($isAdmin)
                    <th class="table-header">Company Commission</th>
                    <th class="table-header">Channel Payouts</th>
                    <th class="table-header">Company Net</th>
                    @else
                    <th class="table-header">My Commission</th>
                    @endif
                    <th class="table-header">Total Settlement</th>
                    <th class="table-header">Total Transactions</th>
                    <th class="table-header">Net Payable</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end" style="font-weight:700;">Annual Totals:</th>
                    <th id="totalAppCount" style="font-weight:700;"></th>
                    <th id="totalDisburse" style="font-weight:700;"></th>
                    @if($isAdmin)
                    <th id="totalCommission" style="font-weight:700;"></th>
                    <th id="totalChannelPayouts" style="font-weight:700;"></th>
                    <th id="totalCompanyNet" style="font-weight:700;"></th>
                    @else
                    <th id="totalChannelPayouts" style="font-weight:700;"></th>
                    @endif
                    <th id="totalSettlement" style="font-weight:700;"></th>
                    <th id="totalTxnCount" style="font-weight:700;"></th>
                    <th id="totalNetPayable" style="font-weight:700;"></th>
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

    function load_data(year) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'month', name: 'month' },
            { data: 'app_count', name: 'app_count' },
            { data: 'total_disburse', name: 'total_disburse' }
        ];

        if (isAdmin) {
            columns.push({ data: 'total_commission', name: 'total_commission' });
            columns.push({ data: 'channel_payouts', name: 'channel_payouts' });
            columns.push({ data: 'company_net', name: 'company_net' });
        } else {
            columns.push({ data: 'channel_payouts', name: 'channel_payouts' });
        }

        columns.push({ data: 'total_settlement', name: 'total_settlement' });
        columns.push({ data: 'txn_count', name: 'txn_count' });
        columns.push({ data: 'net_payable', name: 'net_payable' });

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
            paging: false,
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Monthly Summary Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Monthly Summary Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Monthly Summary Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.monthly-summary') }}",
                data: function(d) {
                    d.year = year || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns,
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalAppCount').html(json.totals.app_count);
                    $('#totalDisburse').html(formatIndianNumber(json.totals.total_disburse));
                    $('#totalChannelPayouts').html(formatIndianNumber(json.totals.channel_payouts));
                    $('#totalSettlement').html(formatIndianNumber(json.totals.total_settlement));
                    $('#totalTxnCount').html(json.totals.txn_count);
                    $('#totalNetPayable').html(formatIndianNumber(json.totals.net_payable));
                    if (isAdmin) {
                        $('#totalCommission').html(formatIndianNumber(json.totals.total_commission));
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
        load_data($('#reportYear').val());

        $('#reportFilterBtn').click(function() {
            var year = $('#reportYear').val();
            $('.data-table').DataTable().destroy();
            load_data(year);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportYear').val('{{ date("Y") }}');
            $('.data-table').DataTable().destroy();
            load_data('{{ date("Y") }}');
        });
    });
</script>
@endsection
