@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/sample-sheet.css')}}">

@endsection
@section('body')

<div class="sample-upload-wrapper">
<div>
    <h3>Please Upload Updated Sample File</h3>
    <form method="POST" action="{{route('sample.upload')}}" enctype="multipart/form-data">
        @csrf
        <div class="file-container" id="cont">
            <input class="input-file" type="hidden" required name="user_id" id="user_id" />
            <input class="input-file" type="file" accept=".csv,.xlsx" required name="csv_file" id="csv_file" />
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
</div>

@endsection

@section('script')
<script>
document.getElementById('csv_file').addEventListener('change', function() {
    document.querySelector('h3').innerText = "File Selected, click Upload";
});
</script>

@endsection