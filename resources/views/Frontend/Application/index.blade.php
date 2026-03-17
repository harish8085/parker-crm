@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/commen.css') }}">

<style>
    .date_range {
        display: none;
        /* Hidden by default */
    }
</style>

@endsection
@section('body')
<div class="card">
    @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning mb-3">{{ session('warning') }}</div>
    @endif
    <div class="application-header">
        <h3 class="application-heading">All Application</h3>
        <div class="btn-container">
            <!-- // user should not not have checker or maker role to access add and upload button -->
            @if(auth()->user()->hasPermission('application','create') || in_array(auth()->user()->roles[0]->id, [35,36]))
            <a href="{{ url('/application/create') }}" style="text-decoration: none;">
                <button class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add
                </button>
            </a>
            @endif

            @if(auth()->user()->hasPermission('application','create') || in_array(auth()->user()->roles[0]->id, [35,36]))
            <a href="{{ url('/application/create/upload') }}" style="text-decoration: none;">
                <button class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload
                </button>
            </a>
            @endif

        </div>
    </div>

    <!-- filter form -->
    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <div class="">
                    <label class="">Date Range</label>
                    <select class="bank-detail-input form-select select date-filter" required name="date" id="date">
                        <option value=""></option>
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
                    <label class="bank-input-label"><b>Date Range</b></label>
                    <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" />
                </div>

            </div>
            @if($user->roles[0]->id == 1)
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label ">Partner Name</label>
                    <select class="bank-detail-input form-select select" required name="partner_name" id="partner_name">
                        <option value=""></option>
                        @foreach($users as $u)
                        @php $identifier = $u->Emp_Id ?: $u->id; @endphp
                        <option value="{{$u->id}}">{{$u->first_name}} {{$u->last_name}} ({{$identifier}})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank Name</label>
                    <select class="bank-detail-input form-select select" required name="bank_name" id="bank_name">
                        <option value="">Select Bank Name</option>
                        @foreach($bank as $b)
                        <option>{{$b->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Product Name</label>
                    <select class="bank-detail-input form-select select" required name="product_name" id="product_name">
                        <option value="">Select Product Name</option>
                        @foreach($product as $p)
                        <option>{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label  ">Status</label>
                    <select class="bank-detail-input form-select select" required name="status" id="status">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
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

    <div class="bank-card" id="actionButton">
        <div class="row">
            <div class="col-lg-4 ml-3 mb-2">
                <button id="bulkDeleteBtn" class="btn btn-danger">Bulk Delete</button>
            </div>
        </div>
    </div>
    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Application.Table.application_table')
    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')
<div class="modal" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Filter</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select User Type<span class="required">*</span></label>
                            <div class="roles-dropdown">
                                <select class="form-select" name="user_type" id="user_type">
                                    <option value="" selected>Select User Type</option>
                                    <option value="channel">Channel Partner</option>
                                    <option value="sales">Sales Person</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row sales">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Sales Person<span class="required">*</span></label>
                            <select class="form-select" name="sales_id" id="sales_id">
                                <option value="">Select Sales Person</option>
                                @foreach($sales as $sale)
                                <option value="{{ $sale->id }}">{{ $sale->first_name }} {{ $sale->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row channel">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Channel Partner<span class="required">*</span></label>
                            <div class="roles-dropdown">
                                <select class="form-select" name="channel_id" id="channel_id">
                                    <option value="">Select Channel Partner</option>
                                    @foreach($channels as $channel)
                                    <option value="{{ $channel->id }}">{{ $channel->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
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
                        <button type="submit" class="save-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Activity Logs Modal --}}
@if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
<div class="modal fade" id="activityLogsModal" tabindex="-1" aria-labelledby="activityLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityLogsModalLabel">Application Activity Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logsLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="logsContent"></div>
                <div id="logsEmpty" class="text-center text-muted py-4" style="display:none;">
                    No activity logs found for this application.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<style>
    .log-timeline { position: relative; padding-left: 30px; }
    .log-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
    .log-entry { position: relative; margin-bottom: 20px; padding: 12px 16px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #007bff; }
    .log-entry.created { border-left-color: #28a745; }
    .log-entry.approved { border-left-color: #007bff; }
    .log-entry.completed { border-left-color: #28a745; }
    .log-entry.rejected, .log-entry.checker_rejected { border-left-color: #dc3545; }
    .log-entry.deleted { border-left-color: #6c757d; }
    .log-entry.updated { border-left-color: #ffc107; }
    .log-entry.status_changed { border-left-color: #17a2b8; }
    .log-entry::before { content: ''; position: absolute; left: -25px; top: 16px; width: 10px; height: 10px; background: #007bff; border-radius: 50%; border: 2px solid #fff; }
    .log-entry .log-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .log-entry .log-user { font-weight: 600; font-size: 14px; }
    .log-entry .log-time { font-size: 12px; color: #6c757d; }
    .log-entry .log-desc { font-size: 13px; color: #333; }
    .log-entry .log-changes { font-size: 12px; color: #666; margin-top: 6px; padding-top: 6px; border-top: 1px solid #e9ecef; }
    .log-entry .log-changes .change-item { margin-bottom: 2px; }
    .log-entry .log-changes .old-val { text-decoration: line-through; color: #dc3545; }
    .log-entry .log-changes .new-val { color: #28a745; font-weight: 500; }
</style>
@endif
@endsection

@section('script')
@include('Frontend.Application.index_js')

@if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
<script type="text/javascript">
function viewLogs(applicationId) {
    var modal = new bootstrap.Modal(document.getElementById('activityLogsModal'));
    $('#logsContent').html('');
    $('#logsEmpty').hide();
    $('#logsLoading').show();
    modal.show();

    $.ajax({
        url: '/application/' + applicationId + '/logs',
        method: 'GET',
        success: function(response) {
            $('#logsLoading').hide();
            if (!response.logs || response.logs.length === 0) {
                $('#logsEmpty').show();
                return;
            }

            var html = '<div class="log-timeline">';
            response.logs.forEach(function(log) {
                html += '<div class="log-entry ' + log.action + '">';
                html += '<div class="log-header">';
                html += '<span class="log-user"><i class="fas fa-user"></i> ' + log.user_name + '</span>';
                html += '<span class="log-time"><i class="fas fa-clock"></i> ' + log.created_at + '</span>';
                html += '</div>';
                html += '<div class="log-desc">' + log.description + '</div>';

                if (log.changes && Object.keys(log.changes).length > 0) {
                    html += '<div class="log-changes">';
                    for (var field in log.changes) {
                        var change = log.changes[field];
                        var fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                        html += '<div class="change-item">';
                        html += '<strong>' + fieldLabel + ':</strong> ';
                        html += '<span class="old-val">' + (change.old || '-') + '</span>';
                        html += ' &rarr; ';
                        html += '<span class="new-val">' + (change.new || '-') + '</span>';
                        html += '</div>';
                    }
                    html += '</div>';
                }

                html += '</div>';
            });
            html += '</div>';
            $('#logsContent').html(html);
        },
        error: function(xhr) {
            $('#logsLoading').hide();
            $('#logsContent').html('<div class="alert alert-danger">Failed to load logs.</div>');
        }
    });
}
</script>
@endif
@endsection
