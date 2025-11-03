@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">

@endsection
@section('body')
<div class="import-header">
    <h2 class="upload-file-heading">Upload Settlement File</h2>
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
    <form method="POST" action="{{url('/settlement/create/upload')}}" enctype="multipart/form-data">
        @csrf
        <div class="file-container" id="cont">
            <input class="input-file" type="file" accept=".csv,.xlsx" required name="xlsx_file" id="xlsx_file" />
            <div class="content-container">
                <img src="{{asset('assets/images/cloud-upload-img.svg')}}" id="img">
                <h4 id="h4">Drag Your Files here <br /> Or</h4>
                <p id="p">Browse</p>
            </div>

        </div>

        <div class="save-btn-container">
            <div class="loader-1">
                <div class="loader-div">
                    <div class="loader"></div>
                </div>
            </div>
            <button class="save-btn" id="submitBtn">Upload</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#submitBtn').click(function() {

            $('#submitBtn').hide();
            $('.loader-1').show();
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


        })
    });
</script>

@endsection