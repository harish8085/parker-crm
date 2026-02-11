@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">

@endsection
@section('body')
<div class="import-header d-block">
    <div class="row">
        <div class="col-sm-6">
            <h2 class="upload-file-heading">Upload Bank MIS File</h2>

        </div>
        <div class="col-sm-3">
            <label for="type" class="form-label">Select Bank <span style="color: red;">*</span></label>
            <select class="form-select" required id="type">
                <option value="" selected disabled>Select Bank</option>
                @foreach($banks as $bank)
                <option value="{{$bank->id}}">{{$bank->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label for="product_type" class="form-label">Select Product <span style="color: red;">*</span></label>
            <select class="form-select" required id="product_type">
                <option value="" selected>Select Product</option>
            </select>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-sm-3 offset-sm-6">
            <label for="bank_mis_month" class="form-label">Select Month-Year <span style="color: red;">*</span></label>
            <input type="month" class="form-control" id="bank_mis_month" placeholder="Select Month-Year" required>
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
    <form method="POST" action="{{url('/upload-mis/create')}}" enctype="multipart/form-data">
        @csrf
        <div class="file-container" id="cont">
            <input class="input-file" type="hidden" required name="bank_id" id="bank_id" />
            <input class="input-file" type="hidden" required name="product_id" id="product_id" />
            <input type="hidden" name="bank_mis_month" id="bank_mis_month_input" required />
            <input class="input-file" type="file" accept=".xlsx" required name="xlsx_file" id="xlsx_file" />
            <div class="content-container">
                <img src="{{asset('assets/images/cloud-upload-img.svg')}}" id="img">
                <h4 id="h4">Drag Your Files here <br /> Or</h4>
                <p id="p">Browse</p>
                <p id="file-error" style="color: red; display: none; margin-top: 10px;">File is required <span style="color: red;">*</span></p>
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

        $('#submitBtn').click(function(e) {
            // Validate required fields
            let bank = $('#type').val();
            let product = $('#product_type').val();
            let month = $('#bank_mis_month').val();
            let file = $('#xlsx_file').val();

            let errors = [];

            if (!bank) {
                errors.push('Please select Bank');
            }
            if (!product) {
                errors.push('Please select Product');
            }
            if (!month) {
                errors.push('Please select Month-Year');
            }
            if (!file) {
                errors.push('Please select File');
                $('#file-error').show();
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fill all required fields:\n\n' + errors.join('\n'));
                return false;
            }

            $('#submitBtn').hide()
            $('.loader-1').show()

        })

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
        $('#type').change(function() {
            $('#bank_id').val($(this).val())
        })

        $('#product_type').change(function() {
            $('#product_id').val($(this).val())
        })

        $('#bank_mis_month').change(function() {
            $('#bank_mis_month_input').val($(this).val())
        })


        $('#type').change(function() {
            if ($('#type').val()) {
                performAjaxRequest('/getAllProduct', 'POST', {
                    bank_id: $('#type').val(),
                }, function(response) {
                    var select = $('#product_type')
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select Product',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value.id,
                            text: `${value.name} (${value.group})`
                        }));
                    });
                });
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