@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .file-preview-item {
        display: inline-block;
        margin: 10px 10px 10px 0;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background-color: #f8f9fa;
        position: relative;
        text-align: center;
        min-width: 120px;
    }
    .file-preview-item .file-icon {
        font-size: 48px;
        margin-bottom: 5px;
        display: block;
    }
    .file-preview-item .file-name {
        font-size: 12px;
        word-break: break-word;
        color: #495057;
        margin-top: 5px;
    }
    .file-preview-item .remove-file {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        border: 2px solid white;
    }
    .file-preview-item .remove-file:hover {
        background-color: #c82333;
    }
    .file-preview-image {
        max-width: 120px;
        max-height: 120px;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    .existing-attachment {
        background-color: #e7f3ff;
    }
</style>
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

        <form action="{{ url('/announcements/update/'.$announcement->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Start Date & Time (optional)</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="starts_at" id="starts_at" value="{{ old('starts_at', optional($announcement->starts_at)->format('m-d-Y')) }}" autocomplete="off">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Expiry Date & Time</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="expires_at" id="expires_at" value="{{ old('expires_at', optional($announcement->expires_at)->format('m-d-Y')) }}" autocomplete="off">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label" for="attachment">Attachments (Image, PDF, or Document)</label>
                        <input type="file" name="attachments[]" id="attachment" class="form-control" multiple
                            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" />
                        <small class="form-text text-muted">
                            Allowed file types: images (JPG, PNG, GIF), PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV.<br>
                            You can select multiple files. Max file size may apply.
                        </small>
                        <div id="file-preview-container" class="mt-3">
                            @if($announcement->attachments && $announcement->attachments->count() > 0)
                                <div class="mb-3">
                                    <strong>Existing Attachments:</strong>
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
                                            @endphp
                                            <div class="file-preview-item existing-attachment" data-attachment-id="{{ $attachment->id }}">
                                                @if($isImage)
                                                    <img src="{{ asset($attachment->attachment) }}" class="file-preview-image" alt="Attachment">
                                                @else
                                                    <i class="fas {{ $fileIcon }} file-icon" style="color: #6c757d;"></i>
                                                @endif
                                                <div class="file-name" title="{{ basename($attachment->attachment) }}">
                                                    {{ strlen(basename($attachment->attachment)) > 15 ? substr(basename($attachment->attachment), 0, 15) . '...' : basename($attachment->attachment) }}
                                                </div>
                                                <span class="remove-existing-file" data-attachment-id="{{ $attachment->id }}" style="position: absolute; top: -8px; right: -8px; background-color: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: 2px solid white;">&times;</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <input type="hidden" name="delete_attachments" id="delete_attachments" value="">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" id="announcement_message_edit" class="form-control" rows="4" required>{{ old('message', $announcement->message) }}</textarea>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    CKEDITOR.replace('announcement_message_edit', {
        height: 200
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Check if datepicker is available
        if (typeof $.fn.datepicker === 'undefined') {
            console.error('Bootstrap datepicker is not loaded');
            return;
        }
        
        // Initialize Start Date picker - cannot select previous dates
        var startDateValue = $('#starts_at').val();
        var initialStartDate = startDateValue ? new Date(startDateValue.split('-')[2], startDateValue.split('-')[0] - 1, startDateValue.split('-')[1]) : new Date();
        
        $('#starts_at').datepicker({
            format: 'm-d-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(), // Prevent selecting past dates
            orientation: 'bottom auto'
        });

        // Initialize Expiry Date picker - must be >= start date
        var expiryDateValue = $('#expires_at').val();
        var initialExpiryDate = expiryDateValue ? new Date(expiryDateValue.split('-')[2], expiryDateValue.split('-')[0] - 1, expiryDateValue.split('-')[1]) : new Date();
        
        $('#expires_at').datepicker({
            format: 'm-d-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: initialStartDate >= new Date() ? initialStartDate : new Date(),
            orientation: 'bottom auto'
        });

        // Update expiry date minimum when start date changes
        $('#starts_at').on('changeDate', function(e) {
            var startDate = e.date;
            if (startDate) {
                $('#expires_at').datepicker('setStartDate', startDate);
                var expiryDate = $('#expires_at').datepicker('getDate');
                if (expiryDate && expiryDate < startDate) {
                    $('#expires_at').val('');
                }
            } else {
                $('#expires_at').datepicker('setStartDate', new Date());
            }
        });

        // Clear start date handler
        $('#starts_at').on('clearDate', function(e) {
            $('#expires_at').datepicker('setStartDate', new Date());
        });

        // File attachment preview functionality
        let selectedFiles = [];
        let deletedAttachmentIds = [];
        
        // Function to get file icon based on extension
        function getFileIcon(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            const iconMap = {
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'gif': 'fa-file-image',
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'xls': 'fa-file-excel',
                'xlsx': 'fa-file-excel',
                'ppt': 'fa-file-powerpoint',
                'pptx': 'fa-file-powerpoint',
                'txt': 'fa-file-alt',
                'csv': 'fa-file-csv'
            };
            return iconMap[ext] || 'fa-file';
        }

        // Function to format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Function to display file preview
        function displayFilePreview(files) {
            const container = $('#file-preview-container');
            // Don't clear existing attachments section, just add new files after it
            
            if (files.length === 0) {
                return;
            }
            
            // Find where to insert new files (after existing attachments section)
            let insertAfter = container.find('.mb-3').length > 0 ? container.find('.mb-3').last() : container;
            
            Array.from(files).forEach((file, index) => {
                const fileIcon = getFileIcon(file.name);
                const fileSize = formatFileSize(file.size);
                const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(file.name.split('.').pop().toLowerCase());
                const fileName = file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name;
                
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const itemHtml = '<div class="file-preview-item" data-index="' + index + '">' +
                            '<img src="' + e.target.result + '" class="file-preview-image" alt="' + file.name + '">' +
                            '<div class="file-name" title="' + file.name + '">' + fileName + '</div>' +
                            '<small class="text-muted">' + fileSize + '</small>' +
                            '<span class="remove-file" data-index="' + index + '">&times;</span>' +
                            '</div>';
                        container.append(itemHtml);
                    };
                    reader.readAsDataURL(file);
                } else {
                    const itemHtml = '<div class="file-preview-item" data-index="' + index + '">' +
                        '<i class="fas ' + fileIcon + ' file-icon" style="color: #6c757d;"></i>' +
                        '<div class="file-name" title="' + file.name + '">' + fileName + '</div>' +
                        '<small class="text-muted">' + fileSize + '</small>' +
                        '<span class="remove-file" data-index="' + index + '">&times;</span>' +
                        '</div>';
                    container.append(itemHtml);
                }
            });
        }

        // Handle file input change
        $('#attachment').on('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                selectedFiles = Array.from(files);
                displayFilePreview(selectedFiles);
            }
        });

        // Handle file removal (new files)
        $(document).on('click', '.remove-file', function() {
            const index = parseInt($(this).data('index'));
            const dt = new DataTransfer();
            const input = document.getElementById('attachment');
            
            selectedFiles.splice(index, 1);
            
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            input.files = dt.files;
            
            // Remove preview item
            $(this).closest('.file-preview-item').remove();
        });

        // Handle existing attachment removal
        $(document).on('click', '.remove-existing-file', function() {
            const attachmentId = $(this).data('attachment-id');
            
            // Add to deleted array if not already present
            if (attachmentId && deletedAttachmentIds.indexOf(attachmentId.toString()) === -1) {
                deletedAttachmentIds.push(attachmentId.toString());
            }
            
            // Update hidden field
            $('#delete_attachments').val(deletedAttachmentIds.join(','));
            
            // Remove from DOM
            $(this).closest('.file-preview-item').remove();
            
            // Debug log (remove in production)
            console.log('Deleted attachment IDs:', deletedAttachmentIds);
            console.log('Hidden field value:', $('#delete_attachments').val());
        });

        // Ensure delete_attachments is set before form submission
        $('form').on('submit', function(e) {
            // Update hidden field one more time before submission
            $('#delete_attachments').val(deletedAttachmentIds.join(','));
            console.log('Form submitting with delete_attachments:', $('#delete_attachments').val());
        });
    });
</script>
@endsection
