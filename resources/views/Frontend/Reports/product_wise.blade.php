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
        <h3 class="application-heading">Product-wise Report</h3>
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
            'action' => route('reports.product-wise'),
            'exportType' => 'product-wise',
            'hideUserFilter' => true
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">Product Name</th>
                    <th class="table-header">Bank Name</th>
                    <th class="table-header">Application Count</th>
                    <th class="table-header">Total Disburse Amount</th>
                    @if($isAdmin)
                    <th class="table-header">Avg Commission Rate</th>
                    <th class="table-header">Avg Sharing Rate</th>
                    @else
                    <th class="table-header">Avg Commission Rate</th>
                    @endif
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end" style="font-weight:700;">Totals:</th>
                    <th id="totalAppCount" style="font-weight:700;"></th>
                    <th id="totalDisburse" style="font-weight:700;"></th>
                    @if($isAdmin)
                    <th></th>
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

    function load_data(date_from, date_to, product_id) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_name', name: 'products.name' },
            { data: 'bank_name', name: 'banks.name' },
            { data: 'app_count', name: 'app_count' },
            { data: 'total_disburse', name: 'total_disburse' }
        ];

        if (isAdmin) {
            columns.push({ data: 'avg_commission_rate', name: 'avg_commission_rate' });
            columns.push({ data: 'avg_sharing_rate_display', name: 'avg_sharing_rate', orderable: false, searchable: false });
        } else {
            columns.push({ data: 'avg_sharing_rate_display', name: 'avg_sharing_rate', orderable: false, searchable: false });
        }

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Product-wise Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Product-wise Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Product-wise Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.product-wise') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                    d.product_id = product_id || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns,
            drawCallback: function(settings) {
                var json = settings.json;
                if (json && json.totals) {
                    $('#totalAppCount').html(json.totals.app_count);
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
            var product_id = $('#reportProductId').val();

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to, product_id);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('#reportProductId').val('');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
