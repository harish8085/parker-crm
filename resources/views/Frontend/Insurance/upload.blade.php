@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
@endsection

@section('body')
<div class="mb-4 mt-4">
    <div class="row flex align-items-center justify-content-between">
        <div class="col-sm-6">
            <h2 class="upload-file-heading">Upload Insurance MIS File</h2>
        </div>
        <div class="col-sm-4">
            <label for="bank_id" class="form-label">Select Bank <span style="color: red;">*</span></label>
            <select class="form-select" required id="bank_id">
                <option value="" selected disabled>Select Bank</option>
                @foreach($banks as $bank)
                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="alert alert-info">
        Required headers: <strong>APPLICATION NO., LOCATION, DISBURSEMENT DATE, CUSTOMER NAME, LOAN AMT, INSURANCE RATE, INSURANCE AMT</strong>
    </div>

    <form method="POST" action="{{ route('insurance.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="bank_id" id="bank_id_input" />
        <div class="file-container" id="cont">
            <input class="input-file" type="file" accept=".xlsx" required name="xlsx_file" id="xlsx_file" />
            <div class="content-container">
                <img src="{{asset('assets/images/cloud-upload-img.svg')}}" id="img">
                <h4 id="h4">Drag Your Files here <br /> Or</h4>
                <p id="p">Browse</p>
            </div>
        </div>
        <div class="save-btn-container">
            <button class="save-btn" id="submitBtn">Upload</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $('#bank_id').on('change', function() {
        $('#bank_id_input').val($(this).val());
    });

    $('#xlsx_file').change(function() {
        if ($(this).val()) {
            $('#cont').addClass('file-container-filled')
            $('#h4').html('Re Upload Your Files here <br /> Or')
            $('#p').html('Remove')
            $('#p').addClass('text-dark')
            $('#img').attr('src', `{{asset('assets/images/delete.svg')}}`);
        } else {
            $('#cont').removeClass('file-container-filled')
            $('#h4').html('Drag Your Files here <br /> Or')
            $('#p').html('Browse')
            $('#p').removeClass('text-dark')
            $('#img').attr('src', `{{asset('assets/images/cloud-upload-img.svg')}}`);
        }
    });
</script>
@endsection

