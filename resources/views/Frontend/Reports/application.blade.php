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
        <h3 class="application-heading">Application Report</h3>
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
            'action' => route('reports.application'),
            'exportType' => 'application',
            'statuses' => ['pending', 'approved', 'completed', 'rejected']
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">User Name</th>
                    <th class="table-header">App ID</th>
                    <th class="table-header">Customer Name</th>
                    <th class="table-header">Bank</th>
                    <th class="table-header">Product</th>
                    <th class="table-header">Disburse Amount</th>
                    @if($isAdmin)
                    <th class="table-header">Commission Rate</th>
                    <th class="table-header">Sharing Commission</th>
                    @else
                    <th class="table-header">Commission Rate</th>
                    @endif
                    <th class="table-header">Status</th>
                    <th class="table-header">Created Date</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end" style="font-weight:700;"><span id="totalCount"></span> Totals:</th>
                    <th id="totalDisburse" style="font-weight:700;"></th>
                    @if($isAdmin)
                    <th colspan="4"></th>
                    @else
                    <th colspan="3"></th>
                    @endif
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

    function load_data(date_from, date_to, user_id, bank_id, product_id, status) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_name', name: 'users.first_name' },
            { data: 'app_id', name: 'applications.app_id' },
            { data: 'customer_name', name: 'applications.customer_name' },
            { data: 'bank_name', name: 'banks.name' },
            { data: 'product_name', name: 'products.name' },
            { data: 'disburse_amount', name: 'applications.disburse_amount' }
        ];

        if (isAdmin) {
            columns.push({ data: 'commission_rate_display', name: 'applications.commission_rate' });
            columns.push({ data: 'sharing_commission_display', name: 'applications.sharing_commission' });
        } else {
            columns.push({ data: 'sharing_commission_display', name: 'applications.sharing_commission' });
        }

        columns.push({ data: 'status', name: 'applications.status' });
        columns.push({ data: 'created_at', name: 'applications.created_at' });

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Application Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Application Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Application Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.application') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.user_id = user_id || '';
                    d.bank_id = bank_id || '';
                    d.product_id = product_id || '';
                    d.status = status || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns,
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalCount').html('(' + json.totals.count + ' records)');
                    $('#totalDisburse').html(formatIndianNumber(json.totals.total_disburse));
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
            var status = $('#reportStatus').val();

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to, user_id, bank_id, product_id, status);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('#reportUserSelect').val(null).trigger('change');
            $('#reportBankId').val('');
            $('#reportProductId').val('');
            $('#reportStatus').val('');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
