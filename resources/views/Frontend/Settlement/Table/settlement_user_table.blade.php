<table class="table table-hover data-table-2">
    <thead>
        <tr>
            @if(isset($p) && in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
            <th class="table-header"><input type="checkbox" id="selectAll"></th>
            @endif
            <th class="table-header">Sr. No.</th>
            <th class="table-header">Application No.</th>
            <th class="table-header">Customer Name</th>
            <th class="table-header">Rate %</th>
            <th class="table-header">Commission Amount</th>
            <th class="table-header">TDS Amount({{ $tdsPercentage }}%)</th>
            <th class="table-header">Net Payable</th>
            <th class="table-header">Advance</th>
            <th class="table-header">Status <img src="{{asset('assets/images/sorting-icon.svg')}}"></th>
            <th class="table-header">Actions</th>
        </tr>
    </thead>
    <tbody>
      
    </tbody>
</table>
