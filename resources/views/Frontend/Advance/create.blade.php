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
            <li class="breadcrumb-item active" aria-current="page">Add Advance</li>
        </ol>
    </nav>
</div>

<div class="card p-4">
    <div class="application-header mb-4">
        <h3 class="application-heading mb-0">Add Advance</h3>
    </div>

    <form method="POST" action="{{ route('advance.store') }}">
        @csrf
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Channel Partner<span class="text-danger">*</span></label>
                    <select class="form-select select user-select" name="user_id" data-placeholder="Select Channel Partner" required>
                        <option value="">Select Channel Partner</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} @if($user->email) ({{ $user->email }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Advance Amount<span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="advance_amount" class="form-control" placeholder="Enter amount"
                        value="{{ old('advance_amount') }}" required>
                    @error('advance_amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Advance type<span class="text-danger">*</span></label>
                    <select class="form-select select" name="advance_type" required>
                        <option value="">Select Advance Type</option>
                        <option value="add" {{ old('advance_type') == 'add' ? 'selected' : '' }}>Add</option>
                        <option value="deduct" {{ old('advance_type') == 'deduct' ? 'selected' : '' }}>Deduct</option>
                    </select>
                    @error('advance_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-lg-12 mb-3">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Remark</label>
                    <textarea name="advance_remark" class="form-control" rows="4" placeholder="Add an optional remark">{{ old('advance_remark') }}</textarea>
                    @error('advance_remark')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('advance.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Advance</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('.user-select').select2({
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
    });
</script>
@endsection

