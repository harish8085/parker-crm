@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Advance Requests</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Advance Requests</h3>
    </div>

    <div class="px-4 pt-2">
        <ul class="nav nav-tabs" id="advanceRequestTabs">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('advance-requests.index', ['tab' => 'pending']) }}">Pending Advance Request</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'completed' ? 'active' : '' }}" href="{{ route('advance-requests.index', ['tab' => 'completed']) }}">Approved Requests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'rejected' ? 'active' : '' }}" href="{{ route('advance-requests.index', ['tab' => 'rejected']) }}">Rejected Request</a>
            </li>
        </ul>
    </div>

    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-3 mb-2">
                <label class="bank-input-label">Channel Partner</label>
                <select class="form-select select user-select" id="user_id">
                    <option value="">All Channel Partners</option>
                </select>
            </div>
            @if($isAdmin)
            <div class="col-lg-3 mb-2">
                <label class="bank-input-label">Checker</label>
                <select class="form-select select checker-select" id="requested_by">
                    <option value="">All Checkers</option>
                </select>
            </div>
            @endif
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Date From</label>
                <input type="date" class="form-control" id="date_from">
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Date To</label>
                <input type="date" class="form-control" id="date_to">
            </div>
            <div class="col-lg-2 mb-2 d-flex align-items-end">
                <button class="btn btn-primary me-2" type="button" id="filter">Filter</button>
                <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
            </div>
        </div>
    </div>

    <div class="table-responsive p-4">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Checker</th>
                    <th>Channel Partner</th>
                    <th>Type</th>
                    <th>Cases</th>
                    <th>Requested Amount</th>
                    <th>Status</th>
                    <th>Request Remark</th>
                    <th>Admin Remark</th>
                    <th>Actioned By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Cases Modal -->
<div class="modal fade" id="requestCasesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Cases</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestCasesBody">
                <div class="text-center py-3">Loading...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.3/bootbox.min.js"></script>
<script>
    const currentTab = @json($tab);
    const isAdmin = @json($isAdmin);

    function loadAdvanceRequestTable(filters = {}) {
        $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: "{{ route('advance-requests.index') }}",
                data: Object.assign({
                    tab: currentTab
                }, filters)
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'requested_by_name', name: 'requested_by_name' },
                { data: 'channel_partner', name: 'channel_partner' },
                { data: 'case_type', name: 'case_type' },
                { data: 'case_count', name: 'case_count' },
                { data: 'requested_amount', name: 'requested_amount' },
                { data: 'status', name: 'status' },
                { data: 'advance_remark', name: 'advance_remark', defaultContent: '-' },
                { data: 'admin_remark', name: 'admin_remark', defaultContent: '-' },
                { data: 'actioned_by', name: 'actioned_by' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });
    }

    function initSelects() {
        $('.user-select').select2({
            placeholder: 'All Channel Partners',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('advance.users.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                }
            }
        });

        if (isAdmin) {
            $('.checker-select').select2({
                placeholder: 'All Checkers',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('advance-requests.checkers.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });
        }
    }

    $(document).ready(function () {
        initSelects();
        loadAdvanceRequestTable();

        $('#filter').on('click', function () {
            loadAdvanceRequestTable({
                user_id: $('#user_id').val(),
                requested_by: isAdmin ? $('#requested_by').val() : '',
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val()
            });
        });

        $('#refresh').on('click', function () {
            $('#user_id').val(null).trigger('change');
            if (isAdmin) {
                $('#requested_by').val(null).trigger('change');
            }
            $('#date_from').val('');
            $('#date_to').val('');
            loadAdvanceRequestTable();
        });

        $(document).on('click', '.approve-request-btn', function () {
            const requestId = $(this).data('request-id');
            bootbox.prompt({
                title: 'Approve this advance request? Optional admin remark:',
                inputType: 'textarea',
                centerVertical: true,
                callback: function (result) {
                    if (result === null) return;
                    $.post("{{ url('/advance-requests') }}/" + requestId + "/approve", {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        admin_remark: result
                    }).done(function (res) {
                        bootbox.alert(res.message || 'Approved successfully.');
                        loadAdvanceRequestTable({
                            user_id: $('#user_id').val(),
                            requested_by: isAdmin ? $('#requested_by').val() : '',
                            date_from: $('#date_from').val(),
                            date_to: $('#date_to').val()
                        });
                    }).fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to approve request.';
                        bootbox.alert(msg);
                    });
                }
            });
        });

        $(document).on('click', '.reject-request-btn', function () {
            const requestId = $(this).data('request-id');
            bootbox.prompt({
                title: 'Enter rejection reason',
                inputType: 'textarea',
                centerVertical: true,
                callback: function (result) {
                    if (result === null) return;
                    if (!result || result.trim() === '') {
                        bootbox.alert('Rejection reason is required.');
                        return;
                    }
                    $.post("{{ url('/advance-requests') }}/" + requestId + "/reject", {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        admin_remark: result.trim()
                    }).done(function (res) {
                        bootbox.alert(res.message || 'Rejected successfully.');
                        loadAdvanceRequestTable({
                            user_id: $('#user_id').val(),
                            requested_by: isAdmin ? $('#requested_by').val() : '',
                            date_from: $('#date_from').val(),
                            date_to: $('#date_to').val()
                        });
                    }).fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to reject request.';
                        bootbox.alert(msg);
                    });
                }
            });
        });

        $(document).on('click', '.view-request-cases-btn', function () {
            const requestId = $(this).data('request-id');
            $('#requestCasesBody').html('<div class="text-center py-3">Loading...</div>');
            $.get("{{ url('/advance-requests') }}/" + requestId + "/cases")
                .done(function (res) {
                    if (!res.cases || !res.cases.length) {
                        $('#requestCasesBody').html('<div class="text-muted">No case mappings in this request.</div>');
                    } else {
                        let html = '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>App ID</th><th>Customer</th><th>Bank</th><th>Product</th><th>Sharing %</th><th>Advance Amount</th></tr></thead><tbody>';
                        res.cases.forEach(function (row) {
                            html += '<tr><td>' + row.app_id + '</td><td>' + row.customer_name + '</td><td>' + row.bank_name + '</td><td>' + row.product + '</td><td>' + row.product_percent + '</td><td>₹' + row.advance_payment_amount + '</td></tr>';
                        });
                        html += '</tbody></table></div>';
                        $('#requestCasesBody').html(html);
                    }
                    new bootstrap.Modal(document.getElementById('requestCasesModal')).show();
                })
                .fail(function () {
                    $('#requestCasesBody').html('<div class="text-danger">Failed to load cases.</div>');
                    new bootstrap.Modal(document.getElementById('requestCasesModal')).show();
                });
        });
    });
</script>
@endsection
