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
            <input class="input-file" type="hidden" name="user_type" id="selected_user_type" />
            <input class="input-file" type="hidden" name="channel_id" id="selected_channel_id" />
            <input class="input-file" type="hidden" name="sales_id" id="selected_sales_id" />
            <input class="input-file" type="hidden" name="associate_id" id="selected_associate_id" />
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
                                @if($role_id == 2)
                                <option value="channel">Channel Partner</option>
                                @elseif(!in_array($role_id, [3,37]))
                                <option value="channel">Channel Partner</option>
                                @endif
                                <option value="associate">Associate Partner</option>
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
                <div class="row associate-channel">
                    <div class="col-12 p-2">
                        <label class="input-label">Select Channel Partner<span class="required">*</span></label>
                        <div class="roles-dropdown">
                            <select class="form-select" name="associate_channel_id" id="associate_channel_id">
                                <option value="" selected disabled>Select Channel Partner</option>
                                @foreach($channels as $channel)
                                <option value="{{$channel->id}}" @if($role_id == 2 && $channel->id == $user_id) selected @endif>{{$channel->first_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row associate">
                    <div class="col-12 p-2">
                        <label class="input-label">Select Associate Partner<span class="required">*</span></label>
                        <select class="form-select" name="associate_id" id="associate_id">
                            <option value="" selected disabled>Select Associate Partner</option>
                        </select>
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
        $('.associate-channel').hide()
        $('.associate').hide()
        var role_id = `{{$role_id}}`
        var user_id = `{{$user_id}}`

        function loadAssociates(channelId) {
            $('#associate_id').html('<option value=\"\" selected disabled>Select Associate Partner</option>');
            if (!channelId) {
                return;
            }
            $.ajax({
                url: '/application/channel/' + channelId + '/associates',
                type: 'GET',
                success: function(response) {
                    $.each(response, function(_, associate) {
                        var emp = associate.emp_id ? associate.emp_id : associate.id;
                        $('#associate_id').append('<option value=\"' + associate.id + '\">' + associate.name + ' (' + emp + ')</option>');
                    });
                }
            });
        }

        function toggleTypeFields() {
            var type = $('#user_type').val();
            $('.channel,.sales,.associate-channel,.associate').hide();

            if (type == 'channel') {
                $('.channel').show();
            } else if (type == 'sales') {
                $('.sales').show();
            } else if (type == 'associate') {
                $('.associate-channel,.associate').show();
                if (Number(role_id) == 2) {
                    $('#associate_channel_id').val(user_id).trigger('change');
                }
            }
        }

        $('#submitBtn').click(function() {
            $('#submitBtn').hide()
            $('.loader-1').show()
        })
        $('#user_type').change(toggleTypeFields)
        $('#associate_channel_id').change(function() {
            loadAssociates($(this).val());
        });

        if (Number(role_id) == 2) {
            $('#user_type').val('channel');
            toggleTypeFields();
        }

        $('#select_user').click(function() {
            var selectedType = $('#user_type').val();
            if (!selectedType) {
                $('#user_type').addClass('is-invalid').focus();
                return false;
            }

            if (selectedType == 'channel') {
                if ($('#channel_id').val()) {
                    $('#user_id').val($('#channel_id').val())
                    $('#selected_user_type').val('channel')
                    $('#selected_channel_id').val($('#channel_id').val())
                    $('#selected_sales_id').val('')
                    $('#selected_associate_id').val('')
                    $('#channel_id').addClass('is-valid').removeClass('is-invalid');
                } else {
                    $('#channel_id').removeClass('is-valid').addClass('is-invalid');
                    $('#channel_id').focus();
                    return false;
                }


            } else if (selectedType == 'sales') {
                if ($('#sales_id').val()) {
                    $('#user_id').val($('#sales_id').val())
                    $('#selected_user_type').val('sales')
                    $('#selected_sales_id').val($('#sales_id').val())
                    $('#selected_channel_id').val('')
                    $('#selected_associate_id').val('')
                    $('#sales_id').addClass('is-valid').removeClass('is-invalid');
                } else {
                    $('#sales_id').removeClass('is-valid').addClass('is-invalid');
                    $('#sales_id').focus();
                    return false;
                }
            } else if (selectedType == 'associate') {
                if (!$('#associate_channel_id').val()) {
                    $('#associate_channel_id').addClass('is-invalid').focus();
                    return false;
                }
                if (!$('#associate_id').val()) {
                    $('#associate_id').addClass('is-invalid').focus();
                    return false;
                }

                $('#user_id').val($('#associate_id').val())
                $('#selected_user_type').val('associate')
                $('#selected_channel_id').val($('#associate_channel_id').val())
                $('#selected_associate_id').val($('#associate_id').val())
                $('#selected_sales_id').val('')
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
                if (Number(role_id) == 3 || Number(role_id) == 37) {
                    $('#user_id').val(user_id)
                    $('#selected_user_type').val(Number(role_id) == 3 ? 'sales' : 'associate')
                    $('#selected_sales_id').val(Number(role_id) == 3 ? user_id : '')
                    $('#selected_associate_id').val(Number(role_id) == 37 ? user_id : '')
                    $('#selected_channel_id').val('')
                } else {
                    if (Number(role_id) == 2) {
                        $('#user_type').val('channel');
                        toggleTypeFields();
                    }
                    $('#myModal').modal('show')
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
