@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<style>
    .mc-grid{gap:22px}
    .qr-side{background:#f3f6ff;border:1px solid #e6ecff;border-radius:10px;padding:20px}
    .qr-title{font-size:14px;font-weight:600;color:#6b7280;text-align:center;margin-bottom:10px}
    .qr-card{background:#fff;border-radius:10px;padding:10px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
    .qr-actions .btn{min-width:130px}
    @media(min-width:992px){.left-pane{padding-right:18px}}
    .success-pill{display:inline-block;background:#22c55e;color:#fff;border-radius:6px;padding:6px 12px;font-size:12px}
    .field-hint{font-size:12px;color:#8a94a6}
    /* mock-style panels */
    .panel-box{background:#fff;border:2px solid #12182610;border-radius:8px;box-shadow:0 4px 14px rgba(18,24,38,0.06)}
    .panel-pad{padding:24px}
    .inner-field{max-width:720px}
    .inner-field .form-control{height:44px}
    .update-wrap{max-width:260px}
</style>
@endsection
@section('body')
@php
    // Encrypt the master code before embedding
    $encryptedCode = '';
    if (!empty($masterCode)) {
        $encryptedCode = \Illuminate\Support\Facades\Crypt::encryptString($masterCode);
    }
@endphp
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">{{auth()->user()->roles[0]->id ==2 ? 'Invite User' : 'Master Code'}}</h3>
    </div>

    <div class="bank-card p-4">
        @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('master-code.update') }}">
            @csrf
            <div class="row">
                @if(!empty($masterCode))
            <div class="col-lg-3 mb-3">
                    <div class="panel-box panel-pad h-100 d-flex flex-column align-items-left justify-content-center">
                       
                        <div class="qr-card mb-2">
                          
                            <img id="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ route('signup') . '?code=' . urlencode($encryptedCode) }}" alt="QR Code" width="220" height="220">
                        </div>
                        <div class="qr-actions d-flex gap-2 mt-1">
                            <a id="download-qr" class="btn btn-sm btn-outline-primary" href="{{ route('master-code.download') }}">Download PNG</a>
                            <a id="email-share" class="btn btn-sm btn-outline-secondary" href="mailto:?subject={{ urlencode('Master Code QR') }}&body={{ urlencode(('Here is the master code: '.($masterCodes->code ?? ''))."%0D%0ADownload the QR PNG here: ".route('master-code.download')) }}">Email</a>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="col-lg-6 mb-3">
                    <div class="panel-box panel-pad">
                        <div class="bank-detail-inputs inner-field">
                            <label class="bank-input-label">Master Code</label>
                            <input id="master-code-input" type="text" class="form-control" name="code" placeholder="Enter new master code..." value="{{ old('code', $masterCode ?? '') }}" required>
                            @error('code')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        @if(auth()->user()->roles[0]->id ==1)
                        <div class="update-wrap mt-3">
                            <button type="submit" class="application-header-btn w-100">
                                <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Update Code
                            </button>
                        </div>
                        @endif
                    <div class="mt-3">
                        <button type="button" class=" btn btn-sm btn-outline-primary w-100" id="share-link-btn">
                            <img src="{{ asset('assets/images/share-icon.svg') }}" alt="Share" style="width:18px; height:18px; vertical-align:middle; margin-right:6px;" onmouseover="Share this link with your team">Share Link
                        </button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.getElementById('share-link-btn').addEventListener('click', async function() {
                                const url = "{{ route('signup') . '?code=' . urlencode($encryptedCode ?? '') }}";
                                @if(!empty($masterCode))
                                if (navigator.share) {
                                    navigator.share({
                                        title: 'Signup Link',
                                        text: 'Here is the signup link with master code:',
                                        url: url
                                    });
                                } else if (navigator.clipboard) {
                                    try {
                                        await navigator.clipboard.writeText(url);
                                        alert('Link copied to clipboard!');
                                    } catch (e) {
                                        prompt('Copy this link:', url);
                                    }
                                } else {
                                    prompt('Copy this link:', url);
                                }
                                @else
                                alert('No master code found!');
                                @endif
                            });
                        });
                    </script>
                    </div>
                </div>

                
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
@endsection

