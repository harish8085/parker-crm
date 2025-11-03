  <table class="table table-hover text-nowrap data-table" id="applicationsTable">
      <thead>
          <tr>
              <th class="table-header "><input type="checkbox"  id="masterCheckbox"> &nbsp;&nbsp;&nbsp;All</th>
              <th class="table-header ">S.NO</th>
              @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
              <th class="table-header">Partner Name</th>
              @endif
              <th class="table-header">Application ID</th>
              <th class="table-header">Customer Name</th>
              <th class="table-header">Bank Name</th>
              <th class="table-header">Product Name</th>
              <th class="table-header">Disburse Amount</th>
              <th class="table-header">Commission Rate</th>
              <th class="table-header">Remark</th>
              <th class="table-header">Status</th>
              <th class="table-header">Actions</th>
          </tr>
      </thead>
      <tbody>

      </tbody>
  </table>