@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">

<style>
        .settlement-type-switch {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 12px;
        }

        .settlement-type-wrap {
                margin-bottom: 8px;
        }

        .settlement-type-caption {
                font-size: 13px;
                color: #6b7280;
                margin-bottom: 8px;
        }

        .settlement-type-tab {
                border: 1px solid #d0d7de;
                border-radius: 12px;
                padding: 12px 14px;
                background: #ffffff;
                color: #1f2937;
                text-decoration: none;
                transition: all 0.2s ease;
                display: block;
        }

        .settlement-type-tab:hover {
                border-color: #94a3b8;
                background: #f8fafc;
        }

        .settlement-type-tab.active {
                border-color: #0ea5e9;
                background: #f0f9ff;
                box-shadow: inset 0 0 0 1px #bae6fd;
        }

        .settlement-type-title {
                display: block;
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
        }

        .settlement-type-note {
                display: block;
                font-size: 12px;
                color: #475569;
                margin-top: 2px;
        }

        @media (max-width: 991px) {
                .settlement-type-switch {
                        grid-template-columns: 1fr;
                }
        }

        .date_range {
                display: none;
                /* Hidden by default */
        }

        .transaction-summary {
                display: none;
                background: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 15px 20px;
                margin-bottom: 15px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .transaction-summary .summary-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                font-size: 14px;
                border-bottom: 1px solid #f0f0f0;
        }

        .transaction-summary .summary-row:last-child {
                border-bottom: none;
        }

        .transaction-summary .summary-row.total {
                border-top: 2px solid #333;
                border-bottom: none;
                font-weight: bold;
                font-size: 18px;
                padding-top: 12px;
                margin-top: 5px;
        }

        .transaction-summary .summary-label {
                color: #0a0000;
                font-weight: 500;
        }

        .transaction-summary .summary-value {
                font-weight: 700;
                color: #333;
        }

        .transaction-summary .summary-value.val-gross {
                color: #333;
        }

        .transaction-summary .summary-value.val-tds {
                color: #555;
        }

        .transaction-summary .summary-value.val-advance {
                color: #dc3545;
        }

        .transaction-summary .summary-value.val-net {
                color: #28a745;
                font-size: 20px;
        }

        #processBtn {
                display: none;
        }
</style>
@endsection
@section('body')


<div class="card ">
        <div class="settlement-header">
                <h3 class="settlement-heading">Settlements</h3>
                <div class="settlement-btn-container">
                        @if(isset($p) && in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
                        <button class="btn btn-primary" id="processBtn" disabled>
                                <i class="fas fa-cog"></i> Process Selected
                        </button>
                        @endif

                        <a href="{{ url('/settlement/create/upload') }}" style="text-decoration: none;">
                                <button class="settlement-header-btn">
                                        <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload
                                </button>
                        </a>
                </div>
        </div>

        @if(isset($p) && in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
        <!-- Channel Advance Balance Info -->
        @if(($channelAdvance ?? 0) > 0)
        <div class="p-4 pb-0">
                <div class="alert alert-warning d-flex align-items-center mb-0" style="border-radius: 8px;">
                        <i class="fas fa-info-circle me-2" style="font-size: 18px;"></i>
                        <span>Channel Advance Balance: <strong>₹ {{ indianNumberFormat($channelAdvance) }}</strong></span>
                </div>
        </div>
        @endif

        <!-- Dynamic totals summary -->
        <div class="p-4 pb-0">
                <div class="transaction-summary" id="transactionSummary">
                        <h6 class="mb-3"><strong>Selected Distribution Summary</strong></h6>
                        <div class="summary-row">
                                <span class="summary-label">{{ $amountLabel ?? 'Commission Amount' }}:</span>
                                <span class="summary-value val-gross" id="sumGross">₹ 0</span>
                        </div>
                        <div class="summary-row">
                                <span class="summary-label">TDS ({{ $tdsPercentage }}%):</span>
                                <span class="summary-value val-tds" id="sumTds">₹ 0</span>
                        </div>

                        <!-- Advance Deduction: available for processing roles -->
                        @if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]) && ($channelAdvance ?? 0) > 0)
                        <div class="summary-row" style="align-items: center;">
                                <span class="summary-label">
                                        <label style="cursor: pointer; margin: 0;">
                                                <input type="checkbox" id="deductAdvanceCheck" style="margin-right: 6px; transform: scale(1.2); cursor: pointer;">
                                                Deduct Advance
                                        </label>
                                </span>
                                <span class="summary-value val-advance" id="sumAdvance">₹ 0</span>
                        </div>
                        <div class="summary-row" id="advanceInputRow" style="display: none; align-items: center; padding: 8px 0;">
                                <span class="summary-label">Advance Amount (max ₹ {{ indianNumberFormat($channelAdvance) }}):</span>
                                <span>
                                        <input type="number" id="advanceAmountInput" class="form-control form-control-sm" style="width: 160px; display: inline-block; font-weight: 600;" step="0.01" min="0" max="{{ $channelAdvance }}" value="{{ $channelAdvance }}">
                                </span>
                        </div>
                        @else
                        <div class="summary-row">
                                <span class="summary-label">Advance Deduction:</span>
                                <span class="summary-value val-advance" id="sumAdvance">₹ 0</span>
                        </div>
                        @endif

                        <div class="summary-row total">
                                <span class="summary-label">Net Payable:</span>
                                <span class="summary-value val-net" id="sumNetPayable">₹ 0</span>
                        </div>
                        <div class="mt-2 text-muted" style="font-size: 12px;">
                                <span id="selectedCount">0</span> distribution(s) selected
                        </div>
                </div>
        </div>
        @endif

        <!-- filter form -->
        <div class="bank-card p-4">
                @if(!($hideSettlementTabs ?? false))
                <div class="settlement-type-wrap">
                        <div class="settlement-type-caption">Choose settlement page</div>
                        <div class="settlement-type-switch">
                                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'commission' ? 'active' : '' }}" data-type="commission" href="#" role="tab">
                                        <span class="settlement-type-title">Commission</span>
                                        <span class="settlement-type-note">Regular payout settlements</span>
                                </a>
                                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'contest' ? 'active' : '' }}" data-type="contest" href="#" role="tab">
                                        <span class="settlement-type-title">Contest</span>
                                        <span class="settlement-type-note">Contest payout settlements</span>
                                </a>
                                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'insurance' ? 'active' : '' }}" data-type="insurance" href="#" role="tab">
                                        <span class="settlement-type-title">Insurance</span>
                                        <span class="settlement-type-note">Insurance payout settlements</span>
                                </a>
                        </div>
                </div>
                <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item me-1">
                                <a class="nav-link settlement-tab {{ ($tab ?? 'pending') === 'pending' ? 'active' : '' }}" data-tab="pending" href="#" role="tab">Pending</a>
                        </li>
                        <li class="nav-item">
                                <a class="nav-link settlement-tab {{ ($tab ?? 'pending') === 'completed' ? 'active' : '' }}" data-tab="completed" href="#" role="tab">Completed</a>
                        </li>
                </ul>
                @endif
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

<!-- Hidden form for processing -->
<form id="processForm" action="{{ url('/transactions/process') }}" method="POST" style="display:none;">
        @csrf
</form>
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

        @php
        $showCheckbox = isset($p) && in_array(auth()->user()->roles[0]->id, [1, 35, 36]);
        @endphp

        var currentTab = @json($tab ?? 'pending');
        var currentSettlementType = @json($settlementType ?? 'commission');

        function load_data(date = '', date_range = '', status = '') {
                var columns = [
                        @if($showCheckbox)
                        {
                                data: 'checkbox',
                                name: 'checkbox',
                                orderable: false,
                                searchable: false
                        },
                        @endif
                        {
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
                                name: 'app_id'
                        },
                        {
                                data: 'customer_name',
                                name: 'customer_name'
                        },
                        {
                                data: 'disbursement_amount',
                                name: 'disbursement_amount'
                        },
                        {
                                data: 'submitted_by',
                                name: 'submitted_by'
                        },
                        {
                                data: 'company_receiving',
                                name: 'company_receiving'
                        },
                        {
                                data: 'received_rate',
                                name: 'received_rate'
                        },
                        {
                                data: 'received_commission',
                                name: 'received_commission'
                        },
                        {
                                data: 'gross_amount',
                                name: 'gross_amount'
                        },
                        {
                                data: 'tds_amount',
                                name: 'tds_amount'
                        },
                        {
                                data: 'net_amount',
                                name: 'net_amount'
                        },
                        {
                                data: 'advance_flag',
                                name: 'advance_flag'
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
                ];

                var table2 = $('.data-table-2').DataTable({
                        debug: false,
                        dom: 'Bfrtip<"bottom"l>',
                        lengthMenu: [
                                [10, 25, 50, 100, 500, -1],
                                [10, 25, 50, 100, 500, 'All']
                        ],
                        buttons: [{
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
                                        exportOptions: {
                                                columns: ':not(:first-child)'
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
                                        exportOptions: {
                                                columns: ':not(:first-child)'
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
                                        exportOptions: {
                                                columns: ':not(:first-child)'
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
                                        p: "{{ $p }}",
                                        tab: currentTab,
                                        settlement_type: currentSettlementType
                                },
                                error: function(xhr, error, thrown) {
                                        console.log(xhr.responseText);
                                },
                        },
                        columns: columns
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

                $(document).on('click', '.settlement-tab', function(e) {
                        e.preventDefault();
                        var tab = $(this).data('tab');
                        if (tab === currentTab) return;
                        currentTab = tab;
                        $('.settlement-tab').removeClass('active');
                        $(this).addClass('active');
                        $('.data-table-2').DataTable().destroy();
                        load_data($('#date').val() || '', $('#date-range-picker').val() || '', $('#status').val() || '');
                });

                $(document).on('click', '.settlement-type-tab', function(e) {
                        e.preventDefault();
                        var settlementType = $(this).data('type');
                        if (settlementType === currentSettlementType) return;
                        currentSettlementType = settlementType;
                        window.location.href = "{{ url('/settlement') }}?p={{ $p }}&settlement_type=" + settlementType + "&tab=" + currentTab;
                });

                @if($showCheckbox)
                // Track selected IDs across pages
                var selectedIds = [];

                // Select All checkbox
                $(document).on('change', '#selectAll', function() {
                        var isChecked = $(this).is(':checked');
                        $('.dist-checkbox').each(function() {
                                $(this).prop('checked', isChecked);
                                var id = $(this).val();
                                if (isChecked && selectedIds.indexOf(id) === -1) {
                                        selectedIds.push(id);
                                } else if (!isChecked) {
                                        selectedIds = selectedIds.filter(function(item) {
                                                return item !== id;
                                        });
                                }
                        });
                        updateSummary();
                });

                // Individual checkbox
                $(document).on('change', '.dist-checkbox', function() {
                        var id = $(this).val();
                        if ($(this).is(':checked')) {
                                if (selectedIds.indexOf(id) === -1) {
                                        selectedIds.push(id);
                                }
                        } else {
                                selectedIds = selectedIds.filter(function(item) {
                                        return item !== id;
                                });
                                $('#selectAll').prop('checked', false);
                        }
                        updateSummary();
                });

                function formatIndianNumber(num) {
                        num = parseFloat(num) || 0;
                        var isNeg = num < 0;
                        num = Math.abs(num);
                        var parts = num.toFixed(2).split('.');
                        var intPart = parts[0];
                        var decPart = parts[1];
                        var lastThree = intPart.substring(intPart.length - 3);
                        var otherNumbers = intPart.substring(0, intPart.length - 3);
                        if (otherNumbers !== '') {
                                lastThree = ',' + lastThree;
                        }
                        var formatted = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree + '.' + decPart;
                        return (isNeg ? '-' : '') + formatted;
                }

                // Get current advance deduction amount
                function getAdvanceDeduction() {
                        var advanceCheckbox = document.getElementById('deductAdvanceCheck');
                        var advanceInput = document.getElementById('advanceAmountInput');
                        if (advanceCheckbox && advanceCheckbox.checked && advanceInput) {
                                return parseFloat(advanceInput.value) || 0;
                        }
                        return 0;
                }

                function updateSummary() {
                        var count = selectedIds.length;
                        $('#selectedCount').text(count);

                        if (count > 0) {
                                $('#transactionSummary').slideDown();
                                $('#processBtn').show().prop('disabled', false);

                                // Client-side calculation from data attributes
                                var clientGross = 0,
                                        clientTds = 0,
                                        clientNet = 0;
                                $('.dist-checkbox:checked').each(function() {
                                        clientGross += parseFloat($(this).data('gross')) || 0;
                                        clientTds += parseFloat($(this).data('tds')) || 0;
                                        clientNet += parseFloat($(this).data('net')) || 0;
                                });

                                var advanceDeduction = getAdvanceDeduction();
                                var tdsPercentage = {{ $tdsPercentage }};
                                if (advanceDeduction > 0) {
                                        var taxableAmount = clientGross - advanceDeduction;
                                        if (taxableAmount < 0) taxableAmount = 0;
                                        clientTds = taxableAmount * (tdsPercentage / 100);
                                        clientTds = Math.round(clientTds * 100) / 100;
                                        var netPayable = taxableAmount - clientTds;
                                } else {
                                        var netPayable = clientNet;
                                }
                                if (netPayable < 0) netPayable = 0;

                                $('#sumGross').text('₹ ' + formatIndianNumber(clientGross));
                                $('#sumTds').text('₹ ' + formatIndianNumber(clientTds));
                                $('#sumAdvance').text('₹ ' + formatIndianNumber(advanceDeduction));
                                $('#sumNetPayable').text('₹ ' + formatIndianNumber(netPayable));
                        } else {
                                $('#transactionSummary').slideUp();
                                $('#processBtn').hide().prop('disabled', true);
                                $('#sumGross').text('₹ 0');
                                $('#sumTds').text('₹ 0');
                                $('#sumAdvance').text('₹ 0');
                                $('#sumNetPayable').text('₹ 0');
                        }
                }

                // Checker advance checkbox toggle
                $(document).on('change', '#deductAdvanceCheck', function() {
                        if ($(this).is(':checked')) {
                                $('#advanceInputRow').slideDown();
                        } else {
                                $('#advanceInputRow').slideUp();
                                $('#advanceAmountInput').val({{ $channelAdvance ?? 0 }});
                        }
                        updateSummary();
                });

                // Advance amount input change
                $(document).on('input', '#advanceAmountInput', function() {
                        var maxAdvance = {{ $channelAdvance ?? 0 }};
                        var val = parseFloat($(this).val()) || 0;
                        if (val > maxAdvance) {
                                $(this).val(maxAdvance);
                        }
                        if (val < 0) {
                                $(this).val(0);
                        }
                        updateSummary();
                });

                // Process button click
                $(document).on('click', '#processBtn', function() {
                        if (selectedIds.length === 0) {
                                alert('Please select at least one distribution.');
                                return;
                        }

                        var selectedNet = 0;
                        $('.dist-checkbox:checked').each(function() {
                                selectedNet += parseFloat($(this).data('net')) || 0;
                        });
                        var advanceAmount = getAdvanceDeduction();
                        if (advanceAmount > selectedNet) {
                                alert('Advance deduction cannot be more than selected net payout amount (₹ ' + selectedNet.toFixed(2) + ').');
                                return;
                        }

                        if (!confirm('Are you sure you want to process ' + selectedIds.length + ' distribution(s) into a transaction?')) {
                                return;
                        }

                        var form = $('#processForm');
                        form.find('input[name^="distribution_ids"]').remove();
                        form.find('input[name="advance_amount"]').remove();

                        selectedIds.forEach(function(id) {
                                form.append('<input type="hidden" name="distribution_ids[]" value="' + id + '">');
                        });

                        // Add advance amount
                        form.append('<input type="hidden" name="advance_amount" value="' + advanceAmount + '">');

                        form.submit();
                });
                @endif
        });
</script>
@endsection
