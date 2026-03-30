@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('advance.index') }}">Advances</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Advance</li>
        </ol>
    </nav>
</div>

<div class="card p-4">
    <div class="application-header mb-4">
        <h3 class="application-heading mb-0">Edit Advance</h3>
    </div>

    <form method="POST" action="{{ route('advance.update', $advance->id) }}">
        @csrf
        <div class="row">
            {{-- 1. Channel Partner --}}
            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Channel Partner<span class="text-danger">*</span></label>
                    <select class="form-select select user-select" name="user_id" data-placeholder="Select Channel Partner" required>
                        <option value="">Select Channel Partner</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id', $advance->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} @if($user->email) ({{ $user->email }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 2. Case Type --}}
            @php
                $oldCaseType = old('case_type', $caseType);
            @endphp
            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Case Type</label>
                    <div>
                        <label class="me-3">
                            <input type="radio" name="case_type" value="no_case" {{ $oldCaseType === 'no_case' ? 'checked' : '' }}> No Case
                        </label>
                        <label>
                            <input type="radio" name="case_type" value="case" {{ $oldCaseType === 'case' ? 'checked' : '' }}> Case
                        </label>
                    </div>
                    @error('case_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 3. Cases (Applications) --}}
            <div class="col-lg-12 mb-3 case-section" style="{{ $oldCaseType === 'case' ? '' : 'display:none;' }}">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Select Cases (Application IDs)</label>
                    <div id="cases-loading" class="text-center py-3" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i> Loading cases...
                    </div>
                    <div id="cases-container" style="display:none;">
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-cases">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-cases">Deselect All</button>
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="select-all-checkbox">
                                        </th>
                                        <th>App ID</th>
                                        <th>Customer Name</th>
                                        <th>Firm Name</th>
                                        <th>Bank</th>
                                        <th>Product</th>
                                        <th>Sharing %</th>
                                        <th>Disburse Amount</th>
                                        <th>Disbursement Date</th>
                                        <th>Location</th>
                                        <th>State</th>
                                        <th>Group</th>
                                        {{-- <th>Fresh/BT</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="cases-list">
                                    <!-- Cases will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">Cases will be loaded based on the selected Channel Partner.</small>
                    </div>
                    <div id="cases-empty" class="text-muted py-3" style="display:none;">
                        No pending cases found for the selected Channel Partner.
                    </div>
                    @error('application_ids')
                        <span class="text-danger d-block">{{ $message }}</span>
                    @enderror
                    @error('application_ids.*')
                        <span class="text-danger d-block">{{ $message }}</span>
                    @enderror
                    @error('case_percentages')
                        <span class="text-danger d-block">{{ $message }}</span>
                    @enderror
                    @error('case_percentages.*')
                        <span class="text-danger d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 4. Advance Amount --}}
            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Advance Amount<span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="advance_amount" id="advance_amount" class="form-control" placeholder="Enter amount"
                        value="{{ old('advance_amount', $latestLog ? $latestLog->advance_amount : $advance->advance_amount) }}" required>
                    @error('advance_amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <small id="calculated-amount-note" class="text-info d-block mt-1"></small>
                    <div id="case-breakdown" class="mt-2" style="display:none;">
                        <small class="text-muted"><strong>Breakdown:</strong></small>
                        <ul id="case-breakdown-list" class="list-unstyled mb-0 small text-muted"></ul>
                    </div>
                </div>
            </div>

            {{-- 5. Remark --}}
            <div class="col-lg-12 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Remark</label>
                    <textarea name="advance_remark" class="form-control" rows="4" placeholder="Add an optional remark">{{ old('advance_remark', $latestLog ? $latestLog->remark : $advance->advance_remark) }}</textarea>
                    @error('advance_remark')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('advance.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Advance</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        const caseSection = $('.case-section');
        const userSelect = $('.user-select');
        const advanceAmountInput = $('#advance_amount');
        const amountNote = $('#calculated-amount-note');
        const caseBreakdown = $('#case-breakdown');
        const caseBreakdownList = $('#case-breakdown-list');
        const casesContainer = $('#cases-container');
        const casesLoading = $('#cases-loading');
        const casesEmpty = $('#cases-empty');
        const casesList = $('#cases-list');
        const oldCasePercents = @json(old('case_percentages', []));
        const savedCasePercents = @json($selectedCasePercents ?? []);
        const rupeeSymbol = '\u20B9';
        const multiplySymbol = '\u00D7';
        
        @php
            $oldAppIds = old('application_ids', $selectedApplicationIds ?? []);
        @endphp
        const preSelectedIds = @json($oldAppIds);

        function toggleCaseSection() {
            const caseType = $('input[name="case_type"]:checked').val();
            if (caseType === 'case') {
                caseSection.show();
                advanceAmountInput.prop('readonly', true);
                if (userSelect.val()) {
                    loadCases();
                }
            } else {
                caseSection.hide();
                casesContainer.hide();
                casesEmpty.hide();
                casesList.empty();
                advanceAmountInput.prop('readonly', false);
                amountNote.text('');
                caseBreakdown.hide();
                caseBreakdownList.empty();
            }
        }

        function loadCases() {
            const userId = userSelect.val();
            if (!userId) {
                casesContainer.hide();
                casesEmpty.hide();
                casesList.empty();
                return;
            }

            casesLoading.show();
            casesContainer.hide();
            casesEmpty.hide();

            $.ajax({
                url: '{{ route('advance.applications.list-by-user') }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    'user_id': userId
                },
                success: function (response) {
                    casesLoading.hide();
                    casesList.empty();

                    if (response.length === 0) {
                        casesEmpty.show();
                        return;
                    }

                    let html = '';
                    response.forEach(function(caseItem) {
                        const disburseAmount = parseFloat(caseItem.disburse_amount || 0).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        const disbursementDate = caseItem.disbursement_date ? new Date(caseItem.disbursement_date).toLocaleDateString('en-IN') : '-';
                        const isChecked = preSelectedIds.includes(caseItem.id.toString()) || preSelectedIds.includes(caseItem.id);
                        const oldPercent = oldCasePercents[caseItem.id] ?? oldCasePercents[String(caseItem.id)] ?? null;
                        const savedPercent = savedCasePercents[caseItem.id] ?? savedCasePercents[String(caseItem.id)] ?? null;
                        const defaultPercent = caseItem.default_share_percent ?? '';
                        const percentVal = oldPercent !== null && oldPercent !== undefined ? oldPercent : (savedPercent !== null && savedPercent !== undefined ? savedPercent : defaultPercent);
                        
                        html += '<tr>';
                        html += '<td><input type="checkbox" name="application_ids[]" class="case-checkbox" value="' + caseItem.id + '"' + (isChecked ? ' checked' : '') + '></td>';
                        html += '<td><strong>' + (caseItem.app_id || '-') + '</strong></td>';
                        html += '<td>' + (caseItem.customer_name || '-') + '</td>';
                        html += '<td>' + (caseItem.customer_firm_name || '-') + '</td>';
                        html += '<td>' + (caseItem.bank_name || '-') + '</td>';
                        html += '<td>' + (caseItem.product_name || '-') + '</td>';
                        html += '<td><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm case-percent-input" data-app-id="' + caseItem.id + '" name="case_percentages[' + caseItem.id + ']" value="' + percentVal + '" placeholder="%"></td>';
                        html += '<td>' + rupeeSymbol + disburseAmount + '</td>';
                        html += '<td>' + disbursementDate + '</td>';
                        html += '<td>' + (caseItem.case_location || '-') + '</td>';
                        html += '<td>' + (caseItem.case_state || '-') + '</td>';
                        html += '<td>' + (caseItem.group || '-') + '</td>';
                        // html += '<td>' + (caseItem.fresh_or_bt || '-') + '</td>';
                        html += '</tr>';
                    });

                    casesList.html(html);
                    casesContainer.show();

                    // Update select-all checkbox state
                    const total = $('.case-checkbox').length;
                    const checked = $('.case-checkbox:checked').length;
                    $('#select-all-checkbox').prop('checked', total === checked && total > 0);

                    // Initial calculation if cases are pre-selected
                    if (checked > 0) {
                        updateCalculatedAmount();
                    }
                },
                error: function (xhr) {
                    casesLoading.hide();
                    casesEmpty.show();
                    casesEmpty.html('<span class="text-danger">Failed to load cases. Please try again.</span>');
                }
            });
        }

        function getSelectedCaseIds() {
            const selected = [];
            $('.case-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            return selected;
        }

        function getSelectedCasePercentages() {
            const map = {};
            $('.case-checkbox:checked').each(function() {
                const appId = $(this).val();
                const input = $('.case-percent-input[data-app-id="' + appId + '"]');
                map[appId] = input.val();
            });
            return map;
        }

        function updateCalculatedAmount() {
            const caseType = $('input[name="case_type"]:checked').val();
            const appIds = getSelectedCaseIds();
            const casePercents = getSelectedCasePercentages();

            if (caseType !== 'case') {
                amountNote.text('');
                caseBreakdown.hide();
                caseBreakdownList.empty();
                return;
            }

            if (appIds.length === 0) {
                amountNote.text('');
                caseBreakdown.hide();
                caseBreakdownList.empty();
                advanceAmountInput.val('');
                return;
            }

            // Show loading
            amountNote.html('<i class="fa fa-spinner fa-spin"></i> Calculating...');
            caseBreakdown.hide();

            $.ajax({
                url: '{{ route('advance.cases.calculate') }}',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'application_ids': appIds,
                    'case_percentages': casePercents,
                    'user_id': userSelect.val()
                },
                success: function (response) {
                    if (response.success) {
                        advanceAmountInput.val(response.total_amount);
                        amountNote.html('<span class="text-success"><strong>Calculated Total: ' + rupeeSymbol + parseFloat(response.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong> for ' + response.cases.length + ' case(s)</span>');
                        
                        // Show breakdown
                        let breakdownHtml = '';
                        response.cases.forEach(function(caseItem) {
                            const disburse = parseFloat(caseItem.disburse_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            const payoutPercent = caseItem.payout_percent ?? '';
                            const sharePercent = caseItem.share_percent ?? caseItem.percent ?? '';
                            breakdownHtml += '<li>' + caseItem.app_id + ' (' + caseItem.customer_name + '): ' + rupeeSymbol + disburse + ' ' + multiplySymbol + ' ' + payoutPercent + '% ' + multiplySymbol + ' ' + sharePercent + '% = ' + rupeeSymbol + parseFloat(caseItem.advance_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</li>';
                        });
                        caseBreakdownList.html(breakdownHtml);
                        caseBreakdown.show();
                    } else if (response.message) {
                        amountNote.html('<span class="text-danger">' + response.message + '</span>');
                        caseBreakdown.hide();
                    }
                },
                error: function (xhr) {
                    let msg = 'Failed to calculate advance amount.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    amountNote.html('<span class="text-danger">' + msg + '</span>');
                    caseBreakdown.hide();
                }
            });
        }

        $('input[name="case_type"]').on('change', function () {
            toggleCaseSection();
        });

        toggleCaseSection();

        userSelect.select2({
            placeholder: 'Select Channel Partner',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('advance.users.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        userSelect.on('change', function () {
            if ($('input[name="case_type"]:checked').val() === 'case') {
                loadCases();
            }
        });

        // Select all / Deselect all functionality
        $('#select-all-checkbox').on('change', function() {
            $('.case-checkbox').prop('checked', $(this).prop('checked'));
            updateCalculatedAmount();
        });

        $('#select-all-cases').on('click', function() {
            $('.case-checkbox').prop('checked', true);
            $('#select-all-checkbox').prop('checked', true);
            updateCalculatedAmount();
        });

        $('#deselect-all-cases').on('click', function() {
            $('.case-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
            updateCalculatedAmount();
        });

        // Update select-all checkbox when individual checkboxes change
        $(document).on('change', '.case-checkbox', function() {
            const total = $('.case-checkbox').length;
            const checked = $('.case-checkbox:checked').length;
            $('#select-all-checkbox').prop('checked', total === checked && total > 0);
            updateCalculatedAmount();
        });

        $(document).on('input', '.case-percent-input', function() {
            if ($(this).closest('tr').find('.case-checkbox').prop('checked')) {
                updateCalculatedAmount();
            }
        });
    });
</script>
@endsection




