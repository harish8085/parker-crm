@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">

@endsection
@section('body')
 
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/application') }}">Applications</a></li>
            <li class="breadcrumb-item active" aria-current="page">Upload MIS</li>
        </ol>
    </nav>
</div>
<div class="import-header">
    <h2 class="upload-file-heading">Upload Channel MIS</h2>
    <div>
        <a href="{{ asset('assets/sample/sample.xlsx') }}" download="sample.xlsx">

            <button class="sample-btn"><img class="sample-icon" src="{{asset('assets/images/download.svg')}}">SampleFile</button>
        </a>
    </div>
</div>

<div>
    <div class="note">
        <p>Please ensure all fields are filled in accurately:</p>
        <ul>
            <li><strong>Application ID:</strong> Enter the unique ID for each application.</li>
            <li><strong>Disbursement Date:</strong> Format as DD-MM-YYYY.</li>
            <li><strong>Customer Information:</strong> Provide full name and firm name of the customer.</li>
            <li><strong>Bank Details:</strong> Include bank name, banker name, and contact details.</li>
            <li>Double-check amounts and statuses before submission.</li>
        </ul>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form method="POST" action="{{url('/application/create/upload')}}" enctype="multipart/form-data">
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
@endsection
@section('modal')
<div class="modal" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px; background : #052c65">
                <h5 class="modal-title" style=" color : white">Select User</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal" onclick="refersh()">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <div class="row">
                    <div class="col-12 p-2">
                        <label class="input-label">Select User Type<span class="required">*</span></label>
                        <div class="roles-dropdown">
                            <select class="form-select" required name="user_type" id="user_type">
                                <option value="" selected disabled>Select User Type</option>
                                <option value="channel">Channel Partner</option>
                                <option value="sales">Sales Person</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row sales">
                    <div class="col-12 p-2">
                        <label class="input-label">Select Sales Person<span class="required">*</span></label>
                        <select class="form-select" required name="channel_sales_id" id="sales_id">
                            <option value="" selected disabled>Select Sales Person</option>
                            @foreach($sales as $sale)
                            <option value="{{$sale->id}}">{{$sale->first_name}} {{$sale->last_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row channel">
                    <div class="col-12 p-2">
                        <label class="input-label">Select Channel Partner<span class="required">*</span></label>
                        <div class="roles-dropdown">
                            <select class="form-select" required name="channel_sales_id" id="channel_id">
                                <option value="" selected disabled>Select Channel Partner</option>
                                @foreach($channels as $channel)
                                <option value="{{$channel->id}}">{{$channel->first_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="save-btn-container">
                    <div class="loader-1">
                        <div class="loader-div">
                            <div class="loader"></div>
                        </div>
                    </div>
                    <button class="save-btn" id="select_user">Select</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('.channel').hide()
        $('.sales').hide()
        var role_id = `{{$role_id}}`
        var user_id = `{{$user_id}}`

        $('#submitBtn').click(function() {
            $('#submitBtn').hide()
            $('.loader-1').show()
        })
        $('#user_type').change(function() {

            if ($(this).val() == 'channel') {
                $('.sales').hide()
                $('.channel').show()

            } else {
                $('.sales').show()
                $('.channel').hide()

            }
        })
        $('#select_user').click(function() {
            if ($('#user_type').val() == 'channel') {
                if ($('#channel_id').val()) {
                    $('#user_id').val($('#channel_id').val())
                    $('#channel_id').addClass('is-valid').removeClass('is-invalid');
                } else {
                    $('#channel_id').removeClass('is-valid').addClass('is-invalid');
                    $('#channel_id').focus();
                    return false;
                }


            } else {
                if ($('#sales_id').val()) {
                    $('#user_id').val($('#sales_id').val())
                    $('#sales_id').addClass('is-valid').removeClass('is-invalid');
                } else {
                    $('#sales_id').removeClass('is-valid').addClass('is-invalid');
                    $('#sales_id').focus();
                    return false;
                }
            }
            $('#myModal').modal('hide')
        })

        $('#csv_file').change(function() {
            if ($(this).val()) {
                $('#cont').addClass('file-container-filled')
                $('#h4').html('Re Upload Your Files here <br /> Or')
                $('#p').html('Remove')
                $('#p').addClass('text-dark')
                $('#img').attr('src', `{{asset('assets/images/delete.svg')}}`);
                if (role_id != 2 && role_id != 3) {

                    $('#myModal').modal('show')
                } else {
                    $('#user_id').val(user_id)
                }
            } else {
                $('#cont').removeClass('file-container-filled')
                $('#h4').html('Drag Your Files here <br /> Or')
                $('#p').html('Browse')
                $('#p').removeClass('text-dark')

                $('#img').attr('src', `{{asset('assets/images/cloud-upload-img.svg')}}`);
            }


        })
    });

    function refersh() {
        location.reload()
    }
</script>

@endsection