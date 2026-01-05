@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
</style>
@endsection

@section('body')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Edit Announcement</h4>
    </div>
    <div class="card-body">
        <form action="{{ url('/announcements/update/'.$announcement->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $announcement->title) }}" required>
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Announcement Category</label>
                        <select name="announcement_category_id" id="announcement_category_id" class="form-select select @error('announcement_category_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('announcement_category_id', $announcement->announcement_category_id) ? '' : 'selected' }}>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('announcement_category_id', $announcement->announcement_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('announcement_category_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Bank</label>
                        <select name="bank_id" id="bank_id" class="form-select select @error('bank_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('bank_id', $announcement->bank_id) ? '' : 'selected' }}>Select Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id', $announcement->bank_id) == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('bank_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Bank Product</label>
                        <select name="product_id" id="product_id" class="form-select select @error('product_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('product_id', $announcement->product_id) ? '' : 'selected' }}>Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $announcement->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}{{ $product->group ? ' ('.$product->group.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <div class="input-group date">
                            <input type="text" class="form-control @error('starts_at') is-invalid @enderror" name="starts_at" id="starts_at" value="{{ old('starts_at', optional($announcement->starts_at)->format('m-d-Y')) }}" autocomplete="off">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        @error('starts_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Expiry Date & Time</label>
                        <div class="input-group date">
                            <input type="text" class="form-control @error('expires_at') is-invalid @enderror" name="expires_at" id="expires_at" value="{{ old('expires_at', optional($announcement->expires_at)->format('m-d-Y')) }}" autocomplete="off">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        @error('expires_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label" for="attachment">Attachments (Image, PDF, or Document)</label>
                        <input type="file" name="attachments[]" id="attachment" class="form-control @error('attachments.*') is-invalid @enderror @error('attachments') is-invalid @enderror" multiple
                            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" />
                        <small class="form-text text-muted">
                            Allowed file types: images (JPG, PNG, GIF), PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV.<br>
                            You can select multiple files. Max file size may apply.
                        </small>
                        @error('attachments.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('attachments')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
                <textarea name="message" id="announcement_message_edit" class="form-control @error('message') is-invalid @enderror" rows="4" required>{{ old('message', $announcement->message) }}</textarea>
                @error('message')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="message_attachment">Message Attachment (Image Only)</label>
                <input type="file" name="message_attachment" id="message_attachment" class="form-control @error('message_attachment') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png" />
                <small class="form-text text-muted">
                    Allowed file types: JPG, JPEG, PNG only. Single file upload. Max file size may apply.
                </small>
                @error('message_attachment')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div id="message-attachment-preview" class="mt-3">
                    @if($announcement->message_attachment)
                        <div class="mb-3">
                            <strong>Current Message Attachment:</strong>
                            <div class="mt-2">
                                <div class="file-preview-item existing-attachment" style="position: relative;">
                                    <img src="{{ asset($announcement->message_attachment) }}" class="file-preview-image" alt="Message Attachment" style="max-width: 200px; max-height: 200px;">
                                    <div class="file-name" title="{{ basename($announcement->message_attachment) }}">
                                        {{ strlen(basename($announcement->message_attachment)) > 20 ? substr(basename($announcement->message_attachment), 0, 20) . '...' : basename($announcement->message_attachment) }}
                                    </div>
                                    <span class="remove-existing-message-attachment" onclick="markMessageAttachmentForDeletion()" style="position: absolute; top: -8px; right: -8px; background-color: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: 2px solid white;">&times;</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <input type="hidden" name="delete_message_attachment" id="delete_message_attachment" value="0">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
    CKEDITOR.replace('announcement_message_edit', {
        height: 200
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.select').select2({
            placeholder: 'Select an option',
            width: '100%'
        });
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

        function loadProducts(bankId, selectedProductId = null) {
            if (!bankId) {
                $('#product_id').html('<option value="" disabled selected>Select Product</option>');
                return;
            }

            $.ajax({
                url: '/getAllProduct',
                type: 'POST',
                data: { bank_id: bankId },
                success: function(response) {
                    var select = $('#product_id');
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select Product',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value.id,
                            text: value.name + (value.group ? ' (' + value.group + ')' : ''),
                            selected: selectedProductId && selectedProductId == value.id
                        }));
                    });
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        $('#bank_id').on('change', function() {
            loadProducts($(this).val());
        });

        const initialBankId = $('#bank_id').val();
        const initialProductId = $('#product_id').val();
        if (initialBankId) {
            loadProducts(initialBankId, initialProductId);
        }

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

        // Message attachment preview functionality (single file)
        $('#message_attachment').on('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = $('#message-attachment-preview');
            
            if (file) {
                const isImage = ['jpg', 'jpeg', 'png'].includes(file.name.split('.').pop().toLowerCase());
                const fileSize = formatFileSize(file.size);
                const fileName = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
                
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove existing attachment preview if any
                        previewContainer.find('.mb-3').remove();
                        
                        const previewHtml = '<div class="file-preview-item" style="position: relative;">' +
                            '<img src="' + e.target.result + '" class="file-preview-image" alt="' + file.name + '" style="max-width: 200px; max-height: 200px;">' +
                            '<div class="file-name" title="' + file.name + '">' + fileName + '</div>' +
                            '<small class="text-muted">' + fileSize + '</small>' +
                            '<span class="remove-file" onclick="clearMessageAttachment()" style="position: absolute; top: -8px; right: -8px; background-color: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: 2px solid white;">&times;</span>' +
                            '</div>';
                        previewContainer.append(previewHtml);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.append('<div class="alert alert-warning">Please select a valid image file (JPG, JPEG, or PNG)</div>');
                    $(this).val('');
                }
            }
        });

        // Function to clear message attachment
        window.clearMessageAttachment = function() {
            $('#message_attachment').val('');
            $('#message-attachment-preview .file-preview-item:not(.existing-attachment)').remove();
        };

        // Function to mark message attachment for deletion
        window.markMessageAttachmentForDeletion = function() {
            $('#delete_message_attachment').val('1');
            $('.remove-existing-message-attachment').closest('.file-preview-item').remove();
        };
    });
</script>
@endsection
