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
        <h4 class="mb-0">Add Announcement</h4>
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

        <form action="{{ url('/announcements/create') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Announcement Category</label>
                        <select name="announcement_category_id" id="announcement_category_id" class="form-select select" required>
                            <option value="" disabled {{ old('announcement_category_id') ? '' : 'selected' }}>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('announcement_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Bank</label>
                        <select name="bank_id" id="bank_id" class="form-select select" required>
                            <option value="" disabled {{ old('bank_id') ? '' : 'selected' }}>Select Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Bank Product</label>
                        <select name="product_id" id="product_id" class="form-select select" required>
                            <option value="" disabled selected>Select Product</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Start Date & Time (optional)</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" autocomplete="off">
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
                            <input type="text" class="form-control" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" autocomplete="off">
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
                        <div id="file-preview-container" class="mt-3"></div>
                    </div>
                </div>
            </div>


            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" id="announcement_message_create" class="form-control" rows="4" required>{{ old('message') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="message_attachment">Message Attachment (Image Only)</label>
                <input type="file" name="message_attachment" id="message_attachment" class="form-control"
                    accept=".jpg,.jpeg,.png" />
                <small class="form-text text-muted">
                    Allowed file types: JPG, JPEG, PNG only. Single file upload. Max file size may apply.
                </small>
                <div id="message-attachment-preview" class="mt-3"></div>
            </div>



            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ url('/announcements') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
    CKEDITOR.replace('announcement_message_create', {
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
        // Check if datepicker is available, if not wait a bit
        if (typeof $.fn.datepicker === 'undefined') {
            console.error('Bootstrap datepicker is not loaded');
            return;
        }
        
        // Initialize Start Date picker - cannot select previous dates
        $('#starts_at').datepicker({
            format: 'm-d-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(), // Prevent selecting past dates
            orientation: 'bottom auto'
        });

        // Initialize Expiry Date picker - must be >= start date
        $('#expires_at').datepicker({
            format: 'm-d-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(), // Initially set to today
            orientation: 'bottom auto'
        });

        // Update expiry date minimum when start date changes
        $('#starts_at').on('changeDate', function(e) {
            var startDate = e.date;
            if (startDate) {
                // Set expiry date minimum to the selected start date
                $('#expires_at').datepicker('setStartDate', startDate);
                
                // If expiry date is before start date, clear it
                var expiryDate = $('#expires_at').datepicker('getDate');
                if (expiryDate && expiryDate < startDate) {
                    $('#expires_at').val('');
                }
            } else {
                // If start date is cleared, set expiry date minimum to today
                $('#expires_at').datepicker('setStartDate', new Date());
            }
        });

        // Clear start date handler - reset expiry date minimum to today
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
            container.empty();
            
            if (files.length === 0) {
                return;
            }
            
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
                // Update selectedFiles with all files from input
                selectedFiles = Array.from(files);
                displayFilePreview(selectedFiles);
            }
        });

        // Handle file removal
        $(document).on('click', '.remove-file', function() {
            const index = parseInt($(this).data('index'));
            const dt = new DataTransfer();
            const input = document.getElementById('attachment');
            
            // Remove file from selectedFiles array
            selectedFiles.splice(index, 1);
            
            // Update the file input
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            input.files = dt.files;
            
            // Refresh preview
            displayFilePreview(selectedFiles);
        });

        // Message attachment preview functionality (single file)
        $('#message_attachment').on('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = $('#message-attachment-preview');
            previewContainer.empty();
            
            if (file) {
                const isImage = ['jpg', 'jpeg', 'png'].includes(file.name.split('.').pop().toLowerCase());
                const fileSize = formatFileSize(file.size);
                const fileName = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
                
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewHtml = '<div class="file-preview-item" style="position: relative;">' +
                            '<img src="' + e.target.result + '" class="file-preview-image" alt="' + file.name + '" style="max-width: 200px; max-height: 200px;">' +
                            '<div class="file-name" title="' + file.name + '">' + fileName + '</div>' +
                            '<small class="text-muted">' + fileSize + '</small>' +
                            '<span class="remove-file" onclick="clearMessageAttachment()" style="position: absolute; top: -8px; right: -8px; background-color: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: 2px solid white;">&times;</span>' +
                            '</div>';
                        previewContainer.html(previewHtml);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.html('<div class="alert alert-warning">Please select a valid image file (JPG, JPEG, or PNG)</div>');
                    $(this).val('');
                }
            }
        });

        // Function to clear message attachment
        window.clearMessageAttachment = function() {
            $('#message_attachment').val('');
            $('#message-attachment-preview').empty();
        };
    });
</script>
@endsection