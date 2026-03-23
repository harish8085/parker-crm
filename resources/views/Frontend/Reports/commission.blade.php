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
        <h3 class="application-heading">Commission Report</h3>
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
            'action' => route('reports.commission'),
            'exportType' => 'commission'
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">Application ID</th>
                    <th class="table-header">Bank</th>
                    <th class="table-header">Product</th>
                    <th class="table-header">Disburse Amount</th>
                    @if($isAdmin)
                    <th class="table-header">Commission Rate</th>
                    <th class="table-header">Commission Amount</th>
                    <th class="table-header">Sharing Commission</th>
                    <th class="table-header">Channel Earning</th>
                    <th class="table-header">Company Net</th>
                    @else
                    <th class="table-header">Commission Rate</th>
                    <th class="table-header">Commission Amount</th>
                    @endif
                    <th class="table-header">Date</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalDisburse" style="font-weight:700;"></th>
                    @if($isAdmin)
                    <th></th>
                    <th id="totalCommission" style="font-weight:700;"></th>
                    <th></th>
                    <th id="totalSharing" style="font-weight:700;"></th>
                    <th id="totalCompanyNet" style="font-weight:700;"></th>
                    @else
                    <th></th>
                    <th id="totalSharing" style="font-weight:700;"></th>
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

    function load_data(date_from, date_to, user_id, bank_id, product_id) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_name', name: 'users.first_name' },
            { data: 'app_id', name: 'applications.app_id' },
            { data: 'bank_name', name: 'banks.name' },
            { data: 'product_name', name: 'products.name' },
            { data: 'disburse_amount', name: 'applications.disburse_amount' }
        ];

        if (isAdmin) {
            columns.push({ data: 'commission_rate_display', name: 'applications.commission_rate' });
            columns.push({ data: 'commission_amount', name: 'commission_amount', orderable: false, searchable: false });
            columns.push({ data: 'sharing_commission_display', name: 'applications.sharing_commission' });
            columns.push({ data: 'sharing_amount', name: 'sharing_amount', orderable: false, searchable: false });
            columns.push({ data: 'company_net', name: 'company_net', orderable: false, searchable: false });
        } else {
            columns.push({ data: 'sharing_commission_display', name: 'applications.sharing_commission' });
            columns.push({ data: 'sharing_amount', name: 'sharing_amount', orderable: false, searchable: false });
        }

        columns.push({ data: 'created_at', name: 'applications.created_at' });

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Commission Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Commission Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Commission Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.commission') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.user_id = user_id || '';
                    d.bank_id = bank_id || '';
                    d.product_id = product_id || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns,
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalDisburse').html(formatIndianNumber(json.totals.disburse_amount));
                    $('#totalSharing').html(formatIndianNumber(json.totals.sharing_commission_amount));
                    if (isAdmin) {
                        $('#totalCommission').html(formatIndianNumber(json.totals.commission_amount));
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
            var bank_id = $('#reportBankId').val();
            var product_id = $('#reportProductId').val();

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to, user_id, bank_id, product_id);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('#reportUserSelect').val(null).trigger('change');
            $('#reportBankId').val('');
            $('#reportProductId').val('');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
