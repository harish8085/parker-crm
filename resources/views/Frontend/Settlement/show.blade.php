@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
@endsection
@section('body')
<h2>View Settlement</h2>

<div class="bank-card">
    <div class="card-top-border">Settlement Summary - {{ $channelUser->first_name ?? '' }} {{ $channelUser->last_name ?? '' }}</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Channel Partner</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $channelUser->first_name ?? '' }} {{ $channelUser->last_name ?? '' }}" disabled />
        </div>

        @if($settlement->status =='completed')
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Settlement Date</label>
            <input class="bank-detail-input form-control" type="date" value="{{$settlement->settlement_date}}" disabled />
        </div>
        @endif

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Total {{ $amountLabel ?? 'Commission Amount' }}</label>
            <input class="bank-detail-input form-control" type="text" value="₹ {{ number_format($settlement->amount, 2) }}" disabled />
        </div>

        @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Total Disbursement Amount</label>
            <input class="bank-detail-input form-control" type="text" value="₹ {{ number_format($settlement->gross_amount, 2) }}" disabled />
        </div>
        @endif

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Status</label>
            <input class="bank-detail-input form-control" type="text" value="{{ ucwords($settlement->status) }}" disabled />
        </div>
    </div>
</div>
<br>

{{-- Application-level Distribution Breakdown --}}
<div class="bank-card">
    <div class="card-top-border">Application-wise Distribution</div>
    <div class="table-responsive p-3">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="table-header">Sr. No.</th>
                    <th class="table-header">Application No.</th>
                    <th class="table-header">Customer Name</th>
                    <th class="table-header">Disbursement Amount</th>
                    <th class="table-header">Submitted By</th>
                    <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Contest Receiving' : 'Company Receiving' }}</th>
                    @if(($settlementType ?? 'commission') === 'contest')
                    <th class="table-header">Contest Amount</th>
                    @endif
                    <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Sharing' : 'Sharing Commission' }}</th>
                    <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Contest Rate' : 'Channel Commission' }}</th>
                    <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Contest Amount' : ($amountLabel ?? 'Commission Amount') }}</th>
                    <th class="table-header">TDS ({{ $tdsPercentage }}%)</th>
                    <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Net Value' : 'Net Payable' }}</th>
                    <th class="table-header">Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($settlement_distributions as $key => $dist)
                @php
                    $app = \App\Models\Application::find($dist->application_id);
                    $contest = (($settlementType ?? 'commission') === 'contest' && !empty($dist->contest_mis_id))
                        ? \App\Models\ContestMis::find($dist->contest_mis_id)
                        : null;
                    $contestReceiving = ($contest && $contest->contest_rate !== null)
                        ? (float) $contest->contest_rate
                        : (($app && $app->commission_rate !== null) ? (float) $app->commission_rate : null);
                    $sharingRate = $dist->received_rate !== null ? (float) $dist->received_rate : null;
                    $contestAmount = (($settlementType ?? 'commission') === 'contest')
                        ? (($dist->rc_commission ?? 0) > 0
                            ? (float) $dist->rc_commission
                            : (($sharingRate && $sharingRate > 0) ? round(((float)($dist->gross_amount ?? 0) / ($sharingRate / 100)), 2) : 0))
                        : null;
                    $channelRate = ($contestReceiving !== null && $sharingRate !== null)
                        ? round($contestReceiving * ($sharingRate / 100), 4)
                        : null;
                @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        @if($app)
                        <a href="{{ url('/application/view/' . $app->id) }}" class="text-primary">{{ $app->app_id }}</a>
                        @else
                        N/A
                        @endif
                    </td>
                    <td>{{ $app->customer_name ?? '-' }}</td>
                    <td>₹ {{ number_format($app->disburse_amount ?? 0, 2) }}</td>
                    <td>
                        @if($app && $app->user_id)
                            @php $submitter = \App\Models\User::find($app->user_id); @endphp
                            {{ $submitter ? $submitter->first_name . ' ' . ($submitter->last_name ?? '') : '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $contestReceiving !== null ? rtrim(rtrim(number_format($contestReceiving, 4, '.', ''), '0'), '.') . '%' : '-' }}</td>
                    @if(($settlementType ?? 'commission') === 'contest')
                    <td>₹ {{ number_format($contestAmount ?? 0, 2) }}</td>
                    @endif
                    <td>{{ $sharingRate !== null ? rtrim(rtrim(number_format($sharingRate, 2, '.', ''), '0'), '.') . '%' : '-' }}</td>
                    <td>{{ $channelRate !== null ? rtrim(rtrim(number_format($channelRate, 4, '.', ''), '0'), '.') . '%' : '-' }}</td>
                    <td>₹ {{ number_format($dist->gross_amount ?? 0, 2) }}</td>
                    <td>₹ {{ number_format($dist->tds ?? 0, 2) }}</td>
                    <td>₹ {{ number_format($dist->amount ?? 0, 2) }}</td>
                    <td>{{ ucwords($dist->payment_status ?? 'Pending') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f5f5f5;">
                    <td colspan="{{ ($settlementType ?? 'commission') === 'contest' ? 9 : 8 }}" class="text-end">Totals:</td>
                    <td>₹ {{ number_format($settlement_distributions->sum('gross_amount'), 2) }}</td>
                    <td>₹ {{ number_format($settlement_distributions->sum('tds'), 2) }}</td>
                    <td>₹ {{ number_format($settlement_distributions->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<br>

{{-- Bank Details --}}
@if($settlement->status !='checker')
<div class="bank-card">
    <div class="card-top-border">Bank Details</div>
    <div id="data">
        @foreach($settlement_distributions->unique('bank_account_id') as $dist)
        @if($dist->bank_account_id)
        @php
            $bankAccount = \App\Models\BankData::find($dist->bank_account_id);
        @endphp
        @if($bankAccount)
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Bank Account</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $bankAccount->holder_name }} ({{ $bankAccount->account_number }})" disabled />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">PAN Card Number</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $bankAccount->pan_number ?? '-' }}" disabled />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Aadhar Number</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $bankAccount->aadhar_number ?? '-' }}" disabled />
            </div>
            @if($dist->utr_number)
            <div class="bank-detail-inputs">
                <label class="bank-input-label">UTR Number</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $dist->utr_number }}" disabled />
            </div>
            @endif
        </div>
        @endif
        @endif
        @endforeach
    </div>
</div>
@endif
@endsection
