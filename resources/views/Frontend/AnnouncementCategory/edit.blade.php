@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
@endsection
@section('body')
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Edit Announcement Category</h3>
    </div>
    <div class="p-4">
        <form action="{{url('announcement-categories/update/' . $announcementCategory->id)}}" method="POST">
            @csrf
            <div class="row">
                <div class="col-12 p-2">
                    <label class="input-label">Category Name<span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Enter category name" name="name" value="{{ old('name', $announcementCategory->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-12 p-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $announcementCategory->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>
            </div>
            <div class="save-btn-container">
                <button type="submit" class="save-btn">Update</button>
                <a href="{{ route('announcement-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

