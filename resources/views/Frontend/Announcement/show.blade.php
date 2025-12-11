@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<style>
    .attachment-item {
        display: inline-block;
        margin: 10px 10px 10px 0;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background-color: #f8f9fa;
        text-align: center;
        min-width: 120px;
        position: relative;
    }
    .attachment-item .file-icon {
        font-size: 48px;
        margin-bottom: 5px;
        display: block;
    }
    .attachment-item .file-name {
        font-size: 12px;
        word-break: break-word;
        color: #495057;
        margin-top: 5px;
    }
    .attachment-image {
        max-width: 200px;
        max-height: 200px;
        border-radius: 5px;
        margin-bottom: 5px;
        cursor: pointer;
    }
    .attachment-link {
        display: inline-block;
        margin-top: 5px;
        color: #007bff;
        text-decoration: none;
        font-size: 12px;
    }
    .attachment-link:hover {
        text-decoration: underline;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .info-value {
        color: #6c757d;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('body')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Announcement Details</h4>
        <a href="{{ url('/announcements') }}" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="info-label">Title</div>
            <div class="info-value">{{ $announcement->title }}</div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="info-label">Category</div>
                <div class="info-value">{{ optional($announcement->category)->name ?? 'Not set' }}</div>
            </div>
            <div class="col-lg-4">
                <div class="info-label">Bank</div>
                <div class="info-value">{{ optional($announcement->bank)->name ?? 'Not set' }}</div>
            </div>
            <div class="col-lg-4">
                <div class="info-label">Bank Product</div>
                <div class="info-value">
                    @if($announcement->product)
                        {{ $announcement->product->name }}{{ $announcement->product->group ? ' ('.$announcement->product->group.')' : '' }}
                    @else
                        Not set
                    @endif
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="info-label">Start Date & Time</div>
                <div class="info-value">
                    {{ $announcement->starts_at ? $announcement->starts_at->format('d-m-Y') : 'Not set' }}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="info-label">Expiry Date & Time</div>
                <div class="info-value">
                    {{ $announcement->expires_at ? $announcement->expires_at->format('d-m-Y') : 'Not set' }}
                </div>
            </div>
        </div>

        @if($announcement->message_attachment)
        <div class="mb-4">
            <div class="info-label">Message Attachment</div>
            <div class="mt-2">
                @php
                    $messageAttachmentUrl = asset($announcement->message_attachment);
                @endphp
                <div class="attachment-item">
                    <img src="{{ $messageAttachmentUrl }}" class="attachment-image" alt="Message Attachment" onclick="window.open('{{ $messageAttachmentUrl }}', '_blank')">
                    <div class="file-name" title="{{ basename($announcement->message_attachment) }}">
                        {{ strlen(basename($announcement->message_attachment)) > 20 ? substr(basename($announcement->message_attachment), 0, 20) . '...' : basename($announcement->message_attachment) }}
                    </div>
                    <a href="{{ $messageAttachmentUrl }}" target="_blank" class="attachment-link">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if($announcement->attachments && $announcement->attachments->count() > 0)
        <div class="mb-4">
            <div class="info-label">Attachments</div>
            <div class="mt-2">
                @foreach($announcement->attachments as $attachment)
                    @php
                        $ext = strtolower(pathinfo($attachment->attachment, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                        $iconMap = [
                            'pdf' => 'fa-file-pdf',
                            'doc' => 'fa-file-word',
                            'docx' => 'fa-file-word',
                            'xls' => 'fa-file-excel',
                            'xlsx' => 'fa-file-excel',
                            'ppt' => 'fa-file-powerpoint',
                            'pptx' => 'fa-file-powerpoint',
                            'txt' => 'fa-file-alt',
                            'csv' => 'fa-file-csv'
                        ];
                        $fileIcon = $iconMap[$ext] ?? 'fa-file';
                        $fileUrl = asset($attachment->attachment);
                    @endphp
                    <div class="attachment-item">
                        @if($isImage)
                            <img src="{{ $fileUrl }}" class="attachment-image" alt="Attachment" onclick="window.open('{{ $fileUrl }}', '_blank')">
                        @else
                            <i class="fas {{ $fileIcon }} file-icon" style="color: #6c757d;"></i>
                        @endif
                        <div class="file-name" title="{{ basename($attachment->attachment) }}">
                            {{ strlen(basename($attachment->attachment)) > 20 ? substr(basename($attachment->attachment), 0, 20) . '...' : basename($attachment->attachment) }}
                        </div>
                        <a href="{{ $fileUrl }}" target="_blank" class="attachment-link">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mb-4">
            <div class="info-label">Message</div>
            <div class="info-value">
                <div style="border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; background-color: #f8f9fa;">
                    {!! $announcement->message !!}
                </div>
            </div>
        </div>

        @if(auth()->user()->id == $announcement->created_by)

        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="info-label">Status</div>
                <div class="info-value">
                    @if($announcement->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="info-label">Views</div>
                <div class="info-value">{{ $announcement->views()->count() }}</div>
            </div>
        </div>

        @if($announcement->creator)
        <div class="mb-4">
            <div class="info-label">Created By</div>
            <div class="info-value">{{ $announcement->creator->first_name . ' ' . $announcement->creator->last_name ?? 'N/A' }}</div>
        </div>
        @endif
       

        <div class="mb-4">
            <div class="info-label">Created At</div>
            <div class="info-value">{{ $announcement->created_at->format('d-m-Y H:i') }}</div>
        </div>
        

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ url('/announcements') }}" class="btn btn-secondary me-2">Back</a>
            <a href="{{ url('/announcements/update/'.$announcement->id) }}" class="btn btn-primary">Edit</a>
        </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    // Add any JavaScript if needed for image modal or other interactions
</script>
@endsection

