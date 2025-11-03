@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">

@endsection
@section('body')
<h2>Edit Remark</h2>
<form action="{{url('/remark-status/update/'.$remark->id)}}" method="POST">
    @method('PUT')
    @csrf
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="bank-card">
        <div class="row m-0 card-top-border">
            <div class="col-sm-9">
                Basic Details
            </div>
        </div>
        <div class="card-form">
        <div class="bank-detail-inputs">
                <label class="bank-input-label">Bank Code<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="title" id="title" placeholder="Enter TItle" value="{{$remark->title}}" required>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Group<span class="required">*</span></label>
                <!-- <input class="bank-detail-input form-control" type="text" name="disbAmount" id="disbAmount" placeholder="Enter Disburse Amount" required> -->
                <select class="bank-detail-input form-select row_select" required name="status" id="status">
                    <option value="" disabled>Select</option>
                    <option value="1" @if($remark->status == 1) selected @endif>Active</option>
                    <option value="0" @if($remark->status == 0) selected @endif>Inactive</option>
                </select>
            </div>
          

        </div>
    </div>
    <br>
    <center>
        <div class="col-sm-12">
            <button type="submit" class="btn btn-success mt-2">Save</button>
        </div>
    </center>

</form>

@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#uploadBtn').click(function() {
            $('#xlsx_file').click();
        });

        $('#xlsx_file').change(function() {
            var fileInput = $(this)[0].files[0];
            var formData = new FormData();
            formData.append('file', fileInput);

            $.ajax({
                url: '/getFileData',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    var select = $('.row_select');
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value,
                            text: value
                        }));
                    });
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });

        $('#bank_id,#group').change(function() {
            if ($('#bank_id').val() && $('#group').val()) {
                performAjaxRequest('/getProduct', 'POST', {
                    bank_id: $('#bank_id').val(),
                    group: $('#group').val(),
                }, function(response) {
                    var select = $('#product_id')
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select Product',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value.id,
                            text: value.name
                        }));
                    });
                });
            }
        });

        $('#submitBtn').click(function(event) {
            // Prevent default form submission
            event.preventDefault();

            // Perform form validation
            var isValid = true;

            if (!$('#dsa_code').val()) {
                $('#dsa_code').removeClass('is-valid').addClass('is-invalid');
                $('#dsa_code').focus();
                isValid = false;
                return false;

            } else {
                $('#dsa_code').addClass('is-valid').removeClass('is-invalid');
            }

           


            if (!$('#bank_id').val()) {
                $('#bank_id').removeClass('is-valid').addClass('is-invalid');
                $('#bank_id').focus();
                $('.invalid-feedback').show()
                isValid = false;
                return false;
            } else {
                $('#bank_id').addClass('is-valid').removeClass('is-invalid');
                $('.invalid-feedback').hide()

            }


            if (!$('#product_id').val()) {
                $('#product_id').removeClass('is-valid').addClass('is-invalid');
                $('#product_id').focus();
                isValid = false;
                return false;

            } else {
                $('#product_id').addClass('is-valid').removeClass('is-invalid');
            }

            if (!$('#group').val()) {
                $('#group').removeClass('is-valid').addClass('is-invalid');
                $('#group').focus();
                isValid = false;
                return false;

            } else {
                $('#group').addClass('is-valid').removeClass('is-invalid');
            }

              // If form is valid, submit the form
            if (isValid) {
                $('.needs-validation').submit();
            }
        });

        function performAjaxRequest(url, type, data, successCallback) {
            $.ajax({
                url: url,
                type: type,
                data: data,
                success: successCallback,
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
</script>
@endsection