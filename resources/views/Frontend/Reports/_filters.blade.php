<div class="card mb-3" style="border:none; border-radius:10px;">
    <div class="card-body" style="padding: 16px 20px;">
        <div class="row align-items-end g-2">
            {{-- Date From --}}
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Date From</label>
                <input type="date" id="reportDateFrom" class="form-control form-control-sm">
            </div>

            {{-- Date To --}}
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Date To</label>
                <input type="date" id="reportDateTo" class="form-control form-control-sm">
            </div>

            {{-- User Select2 AJAX Multi-Select --}}
            @if(!isset($hideUserFilter) || !$hideUserFilter)
            <div class="col-auto" style="min-width: 260px;">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">User</label>
                <select id="reportUserSelect" class="form-select form-select-sm" multiple="multiple">
                </select>
            </div>
            @endif

            {{-- Bank Dropdown --}}
            @if(isset($banks))
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Bank</label>
                <select id="reportBankId" class="form-select form-select-sm">
                    <option value="">All Banks</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Product Dropdown --}}
            @if(isset($products))
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Product</label>
                <select id="reportProductId" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Status Dropdown --}}
            @if(isset($statuses))
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Status</label>
                <select id="reportStatus" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Year Dropdown --}}
            @if(isset($years))
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size:12px; font-weight:600; color:#555;">Year</label>
                <select id="reportYear" class="form-select form-select-sm">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Buttons --}}
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-sm" id="reportFilterBtn" style="font-size:13px;">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="reportRefreshBtn" style="font-size:13px;">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>

            {{-- Export Button --}}
            @if(isset($exportType))
            <div class="col-auto ms-auto">
                <a href="javascript:void(0);" id="reportExportBtn" class="btn btn-success btn-sm" style="font-size:13px;">
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('filter-scripts')
<script>
$(document).ready(function() {
    if ($('#reportUserSelect').length) {
        $('#reportUserSelect').select2({
            placeholder: 'Search and select users...',
            allowClear: true,
            multiple: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("reports.users.search") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                },
                cache: true
            },
            width: '100%'
        });
    }

    @if(isset($exportType))
    $('#reportExportBtn').on('click', function() {
        var params = {};
        var dateFrom = $('#reportDateFrom').val();
        var dateTo = $('#reportDateTo').val();
        var userIds = $('#reportUserSelect').val();
        var bankId = $('#reportBankId').val();
        var productId = $('#reportProductId').val();
        var status = $('#reportStatus').val();
        var year = $('#reportYear').val();

        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (userIds && userIds.length) params.user_id = userIds;
        if (bankId) params.bank_id = bankId;
        if (productId) params.product_id = productId;
        if (status) params.status = status;
        if (year) params.year = year;

        var queryString = $.param(params);
        window.location.href = "{{ route('reports.export', $exportType ?? '') }}" + (queryString ? '?' + queryString : '');
    });
    @endif
});
</script>
@endpush

<style>
    .select2-container {
        min-width: 240px;
    }
    .select2-container--default .select2-selection--multiple {
        min-height: 31px;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        font-size: 13px;
        padding: 1px 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 12px;
        padding: 1px 6px;
        margin-top: 2px;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 13px;
        margin-top: 3px;
    }
</style>
