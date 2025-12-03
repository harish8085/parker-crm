@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
@endsection

@section('body')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Edit Announcement</h4>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ url('/announcements/update/'.$announcement->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" id="announcement_message_edit" class="form-control" rows="4" required>{{ old('message', $announcement->message) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date & Time (optional)</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($announcement->starts_at)->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date & Time</label>
                <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', optional($announcement->expires_at)->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $announcement->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ url('/announcements') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    CKEDITOR.replace('announcement_message_edit', {
        height: 200
    });
</script>
@endsection
