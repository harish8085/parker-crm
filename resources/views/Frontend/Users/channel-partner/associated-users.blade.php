@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/manage-user.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/paginate.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection
@section('body')

<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/channel') }}">Channel Partners</a></li>
            <li class="breadcrumb-item active" aria-current="page">Associated Users</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">Associated Channel Users - {{ $channelPartner->first_name }}</h3>
        <div class="user-btn-container">
            <a href="{{ url('/channel') }}">
                <button class="settlement-header-btn">← Back</button>
            </a>
        </div>
    </div>

    <!-- Channel Partner Personal Details -->
    <div class="bank-card">
        <div class="card-top-border">
            Personal Details
            <div style="float: right;">
                <a href="javascript:void(0);" id="toggleDetailsLink" style="color: white; text-decoration: underline; cursor: pointer;">Show More Details</a>
            </div>
        </div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Channel Name</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->first_name }}" disabled>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Employee ID</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->Emp_Id ?? '-' }}" disabled>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Email</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->email }}" disabled>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Phone Number</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->phone }}" disabled>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Pan Card</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->pan_number ?? '-' }}" disabled>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Aadhar Number</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->aadhar_number ?? '-' }}" disabled>
            </div>
        </div>
    </div>

    <!-- Address Details -->
    @if($channelPartner->address_1 || $channelPartner->address_2 || $channelPartner->state || $channelPartner->district || $channelPartner->pincode)
    <div id="addressDetailsSection" style="display: none;">
        <br>
        <div class="bank-card">
            <div class="card-top-border">Address Details</div>
            <div class="card-form">
                @if($channelPartner->address_1)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Address Line 1</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->address_1 }}" disabled>
                </div>
                @endif
                @if($channelPartner->address_2)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Address Line 2</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->address_2 }}" disabled>
                </div>
                @endif
                @if($channelPartner->landmark)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Landmark</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->landmark }}" disabled>
                </div>
                @endif
                @if($channelPartner->state)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">State</label>
                    <select class="bank-detail-input form-select" disabled>
                        @foreach($states as $stateData)
                        <option value="{{ $stateData['state_code'] }}" {{ ($channelPartner->state == $stateData['state_code']) ? 'selected' : '' }}>{{ $stateData['state'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($channelPartner->district)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">District</label>
                    <select class="bank-detail-input form-select" disabled>
                        @foreach($districts as $districtData)
                        <option value="{{ $districtData }}" {{ ($channelPartner->district == $districtData) ? 'selected' : '' }}>{{ $districtData }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($channelPartner->pincode)
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Pincode</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $channelPartner->pincode }}" disabled>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Bank Details -->
    @if($bank)
    <div id="bankDetailsSection" style="display: none;">
        <br>
        <div class="bank-card">
            <div class="card-top-border">Bank Details</div>
            <div class="card-form">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank Name</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $bank->bank_name ?? '-' }}" disabled>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Branch Name</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $bank->branch_name ?? '-' }}" disabled>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Account Holder Name</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $bank->holder_name ?? '-' }}" disabled>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Account Number</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $bank->account_number ?? '-' }}" disabled>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">IFSC Code</label>
                    <input class="bank-detail-input form-control" type="text" value="{{ $bank->ifsc_code ?? '-' }}" disabled>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Associated Users List -->
    <div class="table-responsive p-4">
        @if($associatedUsers->count() > 0)
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th class="table-header">S.No</th>
                    <th class="table-header">Employee ID</th>
                    <th class="table-header">Name</th>
                    <th class="table-header">Email</th>
                    <th class="table-header">Phone</th>
                    <th class="table-header">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($associatedUsers as $index => $user)
                <tr>
                    <td>{{ ($associatedUsers->currentPage() - 1) * $associatedUsers->perPage() + $index + 1 }}</td>
                    <td>{{ $user->Emp_Id ? $user->Emp_Id : '-' }}</td>
                    <td>{{ $user->first_name ? $user->first_name . ' ' . ($user->last_name ?? '') : '-' }}</td>
                    <td>{{ $user->email ? $user->email : '-' }}</td>
                    <td>{{ $user->phone ? $user->phone : '-' }}</td>
                    <td>
                        <button class='table-status-btn {{ $user->status ? 'completed' : 'rejected' }}'>
                            {{ $user->status ? 'Active' : 'In-Active' }}
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-container">
            {{ $associatedUsers->links() }}
        </div>
        @else
        <div class="text-center p-4">
            <p>No associated users found.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleLink = document.getElementById('toggleDetailsLink');
        const addressSection = document.getElementById('addressDetailsSection');
        const bankSection = document.getElementById('bankDetailsSection');
        
        let isVisible = false;

        toggleLink.addEventListener('click', function() {
            isVisible = !isVisible;

            // Toggle address section if it exists
            if (addressSection) {
                addressSection.style.display = isVisible ? 'block' : 'none';
            }

            // Toggle bank section if it exists
            if (bankSection) {
                bankSection.style.display = isVisible ? 'block' : 'none';
            }

            // Update link text
            toggleLink.textContent = isVisible ? 'Hide Details' : 'Show More Details';
        });
    });
</script>
@endsection
