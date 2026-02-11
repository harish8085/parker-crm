@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<style>
    .date_range {
        display: none;
    }
</style>
@endsection
@section('body')

<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">Transactions</h3>
    </div>

    <!-- Filter form -->
    <div class="bank-card p-4">
        <div class="row">
            @if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Channel</label>
                    <select class="bank-detail-input form-select select" name="channel_id" id="channel_id">
                        <option value="">All Channels</option>
                        @foreach($channels as $channel)
                        <option value="{{ $channel->id }}">{{ $channel->first_name }} {{ $channel->last_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Status</label>
                    <select class="bank-detail-input form-select select" name="status" id="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Date Range</label>
                    <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" placeholder="Select date range" />
                </div>
            </div>
            <div class="col-lg-3 mt-2">
                <div class="d-flex justify-content-end" style="margin-top: 20px;">
                    <button class="btn btn-primary me-2" type="button" id="filter">Filter</button>
                    <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive p-4" id="myTable">
        @include('Frontend.Transaction.Table.transaction_table')
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#date-range-picker').daterangepicker({
            opens: 'right',
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' to ',
                cancelLabel: 'Clear'
            }
        });

        $('#date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@include('Frontend.Transaction.index_js')
@endsection
