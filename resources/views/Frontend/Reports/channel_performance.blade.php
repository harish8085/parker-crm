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
        <h3 class="application-heading">Channel Performance Report</h3>
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
            'action' => route('reports.channel-performance'),
            'exportType' => 'channel-performance',
            'hideUserFilter' => true
        ])
    </div>

    <div class="table-responsive p-4 pt-0">
        <table class="table table-hover text-nowrap data-table" id="reportTable">
            <thead>
                <tr>
                    <th class="table-header">S.NO</th>
                    <th class="table-header">Channel Name</th>
                    <th class="table-header">Total Applications</th>
                    <th class="table-header">Approved</th>
                    <th class="table-header">Completed</th>
                    <th class="table-header">Total Disbursement</th>
                    @if($isAdmin)
                    <th class="table-header">Company Commission</th>
                    <th class="table-header">Channel Earnings</th>
                    <th class="table-header">Company Net</th>
                    @else
                    <th class="table-header">My Earnings</th>
                    @endif
                    <th class="table-header">Total Settlement</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    var table;
    var isAdmin = @json($isAdmin);

    function load_data(date_from, date_to) {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'channel_name', name: 'first_name' },
            { data: 'total_applications', name: 'total_applications' },
            { data: 'approved_applications', name: 'approved_applications' },
            { data: 'completed_applications', name: 'completed_applications' },
            { data: 'total_disburse', name: 'total_disburse' }
        ];

        if (isAdmin) {
            columns.push({ data: 'total_commission', name: 'total_commission' });
            columns.push({ data: 'channel_earnings', name: 'channel_earnings' });
            columns.push({ data: 'company_net', name: 'company_net', orderable: false, searchable: false });
        } else {
            columns.push({ data: 'channel_earnings', name: 'channel_earnings' });
        }

        columns.push({ data: 'total_settlement', name: 'total_settlement' });

        table = $('.data-table').DataTable({
            processing: true,
            serverSide: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
            buttons: [
                { extend: 'csvHtml5', text: 'CSV', title: 'Channel Performance Report', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', title: 'Channel Performance Report', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: 'Print', title: 'Channel Performance Report', exportOptions: { columns: ':visible' } }
            ],
            ajax: {
                url: "{{ route('reports.channel-performance') }}",
                data: function(d) {
                    d.date_from = date_from || '';
                    d.date_to = date_to || '';
                },
                error: function(xhr) { console.log(xhr.responseText); }
            },
            columns: columns
        });
    }

    $(document).ready(function() {
        load_data();

        $('#reportFilterBtn').click(function() {
            var date_from = $('#reportDateFrom').val();
            var date_to = $('#reportDateTo').val();

            $('.data-table').DataTable().destroy();
            load_data(date_from, date_to);
        });

        $('#reportRefreshBtn').click(function() {
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>
@endsection
