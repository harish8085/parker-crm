<table class="table table-hover data-table-2">
    <thead>
        <tr>
            @if(isset($p) && in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
            <th class="table-header"><input type="checkbox" id="selectAll"></th>
            @endif
            <th class="table-header">Sr. No.</th>
            <th class="table-header">Application No.</th>
            <th class="table-header">Customer Name</th>
            <th class="table-header">Disbursement Amount</th>
            <th class="table-header">Submitted By</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Contest Receiving' : 'Company Receiving' }}</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Sharing' : 'Sharing Commission' }}</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Contest Rate' : 'Channel Commission' }}</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Contest Amount' : ($amountLabel ?? 'Commission Amount') }}</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'TDS' : 'TDS Amount' }}({{ $tdsPercentage }}%)</th>
            <th class="table-header">{{ ($settlementType ?? 'commission') === 'contest' ? 'Net Value' : 'Net Payable' }}</th>
            <th class="table-header">Advance</th>
            <th class="table-header">Status <img src="{{asset('assets/images/sorting-icon.svg')}}"></th>
            <th class="table-header">Actions</th>
        </tr>
    </thead>
    <tbody>
      
    </tbody>
</table>
