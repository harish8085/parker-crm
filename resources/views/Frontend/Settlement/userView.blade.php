@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">

<style>
        .date_range {
                display: none;
                /* Hidden by default */
        }
</style>
@endsection
@section('body')


<div class="card ">
        <div class="settlement-header">
                <h3 class="settlement-heading">Settlements</h3>
                <div class="settlement-btn-container">

                        <a href="{{ url('/settlement/create/upload') }}" style="text-decoration: none;">
                                <button class="settlement-header-btn">
                                        <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload
                                </button>
                        </a>
                </div>
        </div>

        <!-- filter form -->
        <div class="bank-card p-4">
                <div class="row">
                        <div class="col-lg-4 mb-2">
                                <div class="bank-detail-inputs">
                                        <label class="bank-input-label">Date Range</label>
                                        <select class="bank-detail-input form-select select" required name="date" id="date">
                                                <option value="" selected disabled></option>
                                                <option value="custom">Custom</option>
                                                <option value="today">Today</option>
                                                <option value="yesterday">Yesterday</option>
                                                <option value="this_week">This Week</option>
                                                <option value="last_week">Last Week</option>
                                                <option value="this_month">This Month</option>
                                                <option value="last_month">Last Month</option>
                                                <option value="last_3months">Last 3 months</option>
                                                <option value="last_6months">Last 6 months</option>
                                                <option value="this_year">This Year</option>
                                                <option value="last_year">Last Year</option>
                                        </select>
                                </div>
                                <div class="bank-detail-inputs date_range">
                                        <label class="bank-input-label">Date Range</label>
                                        <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" />
                                </div>

                        </div>
                        <div class="col-lg-4 mb-2">
                                <div class="bank-detail-inputs">
                                        <label class="bank-input-label">Status</label>
                                        <select class="bank-detail-input form-select select" required name="status" id="status">
                                                <option value="">Select Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="checker">Checker</option>
                                        </select>
                                </div>
                        </div>
                        <div class="col-lg-12 mt-2">
                                <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary me-2" type="submit" name="filter" id="filter">Filter</button>
                                        <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
                                </div>
                        </div>
                </div>
        </div>

        <div class="table-responsive p-4" id="myTable">
                @include('Frontend.Settlement.Table.settlement_user_table')
        </div>
</div>
@endsection
@section('modal')
<div class="modal" id="myModal">
        <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header" style="padding: 2px 15px;">
                                <h5 class="modal-title">Filter</h5>
                                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                                        <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                                </button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body" style="padding: 20px 25px;">
                                <form>
                                        <div class="row">
                                                <div class="col-12 p-2">
                                                        <label class="input-label">From Date <span class="required">*</span></label>
                                                        <input type="date" class="form-control" placeholder="Enter from date" name="from" id="from">
                                                </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-12 p-2">
                                                        <label class="input-label">To Date<span class="required">*</span></label>
                                                        <input type="date" class="form-control" placeholder="Enter from date" name="to" id="to">
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="col-12 p-2">
                                                        <label class="input-label">Status<span class="required">*</span></label>
                                                        <div class="roles-dropdown">
                                                                <select class="form-select" name="status" id="status">
                                                                        <option selected>All</option>
                                                                        <option value="pending">Pending</option>
                                                                        <option value="rejected">Rejected</option>
                                                                        <option value="completed">Completed</option>
                                                                </select>
                                                        </div>
                                                </div>
                                        </div>

                                        <div class="save-btn-container">
                                                <button class="save-btn">Save</button>
                                        </div>
                                </form>
                        </div>
                </div>
        </div>
</div>
@endsection
@section('script')

<!-- Date picker -->
<script type="text/javascript">
        $(document).ready(function() {
                // Initialize the date range picker
                $('#date-range-picker').daterangepicker({
                        opens: 'right',

                        locale: {
                                format: 'YYYY-MM-DD',
                                separator: ' to '
                        }
                });

                // Show or hide the date range picker based on the selected option
                $('#date').on('change', function() {
                        var val = this.value;
                        if (val == 'custom') {
                                $('.date_range').show(); // Show date range picker
                        } else {
                                $('.date_range').hide(); // Hide date range picker
                        }
                });
        });
</script>

<!-- Datatable -->
<script type="text/javascript">
                $.fn.dataTable.ext.errMode = 'none';

        function load_data(date = '', date_range = '', status = '') {
                var table2 = $('.data-table-2').DataTable({
                        debug: false, // Disable debugging
                        dom: 'Bfrtip<"bottom"l>', // 'l' adds the "Show entries" dropdown
                        lengthMenu: [
                                [10, 25, 50, 100, 500, -1],
                                [10, 25, 50, 100, 500, 'All']
                        ], // Options for the "Show entries" dropdown
                        buttons: [
                                {
                                        extend: 'csvHtml5',
                                        text: 'CSV',
                                        title: 'Settlements',
                                        charset: 'UTF-8',
                                        bom: true,
                                        title: function() {
                                                if (date == "custom") {
                                                        return date_range ? ' Settlement Details From Date : ' + date_range : 'Settlement Details';
                                                }
                                                return date ? ' Settlement Details From Date : ' + date : 'Settlement Details';
                                        },
                                        customize: function(csv) {
                                                var header = '';
                                                if (date || date_range || status) {
                                                        var date = $("#date option:selected").html();
                                                        if (date) header += 'Date: ' + date + '\n';
                                                        if (date_range == "custom") header += 'Date Range: ' + date_range + '\n';
                                                        if (status) header += 'Status: ' + status + '\n';
                                                }
                                                return header + csv; // Prepend the filter information to the CSV content
                                        }
                                },
                                {
                                        extend: 'excelHtml5',
                                        text: 'Excel',
                                        title: 'Settlements',
                                        title: function() {
                                                if (date == "custom") {
                                                        return date_range ? ' Settlement Details From Date : ' + date_range : 'Settlement Details';
                                                }
                                                return date ? ' Settlement Details From Date : ' + date : 'Settlement Details';
                                        },
                                        customize: function(xlsx) {
                                                var sheet = xlsx.xl.worksheets['sheet1.xml']; // Access the sheet XML
                                                // Construct the custom header
                                                var header = '';
                                                if (date || date_range || status) {
                                                        var date = $("#date option:selected").html();
                                                        if (date) header += 'Date: ' + date + '\n';
                                                        if (date_range == "custom") header += 'Date Range: ' + date_range + '\n';
                                                        if (status) header += 'Status: ' + status + '\n';
                                                }
                                                // Add the header in the first row
                                                var rows = $('row', sheet); // Get all rows
                                                var firstRow = rows[0]; // Access the first row
                                                var newRow = '<row r="1">' +
                                                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                                                        '</row><row r="2">' +
                                                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                                                        '</row>';

                                                $(firstRow).before(newRow); // Insert the custom header row before the first row
                                        }
                                },
                                {
                                        extend: 'print',
                                        text: 'Print',
                                        title: 'Settlements',
                                        title: function() {
                                                if (date == "custom") {
                                                        return date_range ? ' Settlement Details From Date : ' + date_range : 'Settlement Details';
                                                }
                                                return date ? ' Settlement Details From Date : ' + date : 'Settlement Details';
                                        },
                                        customize: function(win) {
                                                var filters = '';
                                                if (date || date_range || status) {
                                                        var date = $("#date option:selected").html();
                                                        filters += '<h4>Filters Applied:</h4>';
                                                        if (date) filters += '<p>Date: ' + date + '</p>';
                                                        if (date_range == "custom") filters += '<p>Date Range: ' + date_range + '</p>';
                                                        if (status) filters += '<p>Status: ' + status + '</p>';
                                                }

                                                $(win.document.body).prepend(filters);
                                        }
                                },
                        ],
                        processing: true,
                        serverSide: true,
                        ajax: {
                                url: "{{ route('settlement.index') }}",
                                data: {
                                        date: date,
                                        date_range: date_range,
                                        status: status,
                                        p: "{{ $p }}"
                                },
                                error: function(xhr, error, thrown) {
                                        console.log(xhr.responseText);
                                },
                        },
                        columns: [{
                                        data: null,
                                        name: 'srno',
                                        render: function(data, type, row, meta) {
                                                return meta.row + 1 + meta.settings._iDisplayStart;
                                        },
                                        orderable: false,
                                        searchable: false
                                },
                                {
                                        data: 'app_id',
                                        name: 'application_id'
                                },
                                {
                                        data: 'customer_name',
                                        name: 'customer_name'
                                },
                                {
                                        data: 'received_rate',
                                        name: 'received_rate'
                                },
                                {
                                        data: 'tds_amount',
                                        name: 'tds_amount'
                                },
                                {
                                        data: 'amount',
                                        name: 'amount'
                                },
                                {
                                        data: 'status',
                                        name: 'status'
                                },
                                {
                                        data: 'action',
                                        name: 'action',
                                        orderable: false,
                                        searchable: false
                                },
                        ]
                });
        };

        $(document).ready(function() {
                load_data();

                $('.select').select2({
                        placeholder: "Select an option",
                        allowClear: true
                });

                $('#filter').click(function() {
                        var date = $('#date').val();
                        var date_range = $('#date-range-picker').val();
                        var status = $('#status').val();

                        if (date || status) {
                                $('.data-table-2').DataTable().destroy();
                                load_data(date, date_range, status);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });
</script>
@endsection