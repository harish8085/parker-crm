@extends('Layout.app')
@php
use Illuminate\Support\Facades\Storage;
@endphp
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<style>
    .document-preview {
        margin-top: 10px;
    }
    .document-preview img {
        max-width: 100%;
        max-height: 400px;
        height: auto;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 8px;
        background: #f9f9f9;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .document-preview img:hover {
        transform: scale(1.02);
        border-color: #007bff;
    }
    .document-link {
        display: inline-block;
        margin-top: 10px;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }
    .document-link:hover {
        text-decoration: underline;
        color: #0056b3;
    }
    .document-preview .pdf-icon {
        font-size: 48px;
        color: #dc3545;
        margin-bottom: 10px;
    }
    /* Image Modal Styles */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.9);
    }
    .image-modal-content {
        margin: auto;
        display: block;
        width: 90%;
        max-width: 1200px;
        margin-top: 50px;
        animation: zoom 0.3s;
    }
    @keyframes zoom {
        from {transform: scale(0)}
        to {transform: scale(1)}
    }
    .image-modal-close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
    .image-modal-close:hover {
        color: #bbb;
    }
</style>
@endsection
@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('link-bank.index') }}">Link Banks</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Bank Account</li>
        </ol>
    </nav>
</div>

<div class="bank-card">
    <div class="card-top-border">Bank Account Details</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Bank Name</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $bank->bank_name ?? '-' }}" disabled>
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
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Branch Name</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $bank->branch_name ?? '-' }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Status</label>
            <div>
                @if($bank->status == 1)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Created At</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $bank->created_at ? $bank->created_at->format('Y-m-d H:i:s') : '-' }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Updated At</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $bank->updated_at ? $bank->updated_at->format('Y-m-d H:i:s') : '-' }}" disabled>
        </div>
    </div>
</div>

<br>

<div class="bank-card">
    <div class="card-top-border">Document Uploads</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">PAN Photo</label>
            @if($bank->pan_photo)
                <div class="document-preview">
                    @php
                        $extension = strtolower(pathinfo($bank->pan_photo, PATHINFO_EXTENSION));
                        $isPdf = $extension == 'pdf';
                        $imageUrl = Storage::disk('public')->url($bank->pan_photo);
                    @endphp
                    @if($isPdf)
                        <div class="text-center">
                            <i class="fas fa-file-pdf pdf-icon"></i>
                            <br>
                            <a href="{{ $imageUrl }}" target="_blank" class="document-link">
                                <i class="fas fa-external-link-alt"></i> View PAN PDF
                            </a>
                        </div>
                    @else
                        <img src="{{ $imageUrl }}" alt="PAN Photo" class="view-image" onclick="openImageModal('{{ $imageUrl }}')">
                        <br>
                        <a href="javascript:void(0)" onclick="openImageModal('{{ $imageUrl }}')" class="document-link">
                            <i class="fas fa-expand"></i> Click to View Full Size
                        </a>
                    @endif
                </div>
            @else
                <span class="text-muted">No PAN photo uploaded</span>
            @endif
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Aadhar Photo</label>
            @if($bank->aadhar_photo)
                <div class="document-preview">
                    @php
                        $extension = strtolower(pathinfo($bank->aadhar_photo, PATHINFO_EXTENSION));
                        $isPdf = $extension == 'pdf';
                        $imageUrl = Storage::disk('public')->url($bank->aadhar_photo);
                    @endphp
                    @if($isPdf)
                        <div class="text-center">
                            <i class="fas fa-file-pdf pdf-icon"></i>
                            <br>
                            <a href="{{ $imageUrl }}" target="_blank" class="document-link">
                                <i class="fas fa-external-link-alt"></i> View Aadhar PDF
                            </a>
                        </div>
                    @else
                        <img src="{{ $imageUrl }}" alt="Aadhar Photo" class="view-image" onclick="openImageModal('{{ $imageUrl }}')">
                        <br>
                        <a href="javascript:void(0)" onclick="openImageModal('{{ $imageUrl }}')" class="document-link">
                            <i class="fas fa-expand"></i> Click to View Full Size
                        </a>
                    @endif
                </div>
            @else
                <span class="text-muted">No Aadhar photo uploaded</span>
            @endif
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Passbook Photo</label>
            @if($bank->passbook_photo)
                <div class="document-preview">
                    @php
                        $extension = strtolower(pathinfo($bank->passbook_photo, PATHINFO_EXTENSION));
                        $isPdf = $extension == 'pdf';
                        $imageUrl = Storage::disk('public')->url($bank->passbook_photo);
                    @endphp
                    @if($isPdf)
                        <div class="text-center">
                            <i class="fas fa-file-pdf pdf-icon"></i>
                            <br>
                            <a href="{{ $imageUrl }}" target="_blank" class="document-link">
                                <i class="fas fa-external-link-alt"></i> View Passbook PDF
                            </a>
                        </div>
                    @else
                        <img src="{{ $imageUrl }}" alt="Passbook Photo" class="view-image" onclick="openImageModal('{{ $imageUrl }}')">
                        <br>
                        <a href="javascript:void(0)" onclick="openImageModal('{{ $imageUrl }}')" class="document-link">
                            <i class="fas fa-expand"></i> Click to View Full Size
                        </a>
                    @endif
                </div>
            @else
                <span class="text-muted">No Passbook photo uploaded</span>
            @endif
        </div>
    </div>
</div>

<br>

<div class="save-btn-container">
    <a href="{{ route('link-bank.index') }}" class="btn btn-secondary">Back to List</a>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
    <img class="image-modal-content" id="modalImage">
</div>
@endsection

@section('script')
<script>
    function openImageModal(imageUrl) {
        var modal = document.getElementById('imageModal');
        var modalImg = document.getElementById('modalImage');
        modal.style.display = 'block';
        modalImg.src = imageUrl;
    }

    function closeImageModal() {
        document.getElementById('imageModal').style.display = 'none';
    }

    // Close modal when clicking outside the image
    window.onclick = function(event) {
        var modal = document.getElementById('imageModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endsection

