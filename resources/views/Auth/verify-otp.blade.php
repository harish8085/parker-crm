<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('assets/css/login.css')}}">
    <link rel="icon" type="image/x-icon" href="{{asset('assets/images/favicon.webp')}}">

    <!-- Latest compiled and minified CSS bootstrap 5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>{{env('APP_NAME')}} | Verify OTP</title>
</head>

<body>
    <div class="container login-container">
        <div class="card login-card">
            <h3 class="card-heading">Verify Email</h3>
            <p class="login-pg">We've sent a verification code to <strong>{{ $email ?? session('email') ?? '' }}</strong></p>
            <p class="login-pg" style="font-size: 14px; color: #666;">Please enter the 6-digit code to verify your email address.</p>
            
            <form method="POST" action="{{url('/verify-otp')}}" id="otpForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user_id ?? session('user_id') ?? '' }}">
                <input type="hidden" name="email" value="{{ $email ?? session('email') ?? '' }}">
                
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(Session::has('error'))
                <div class="alert alert-danger" id="errorMsg">
                    {{ Session::get('error') }}
                </div>
                @endif

                @if(Session::has('success'))
                <div class="alert alert-success" id="successMsg">
                    {{ Session::get('success') }}
                </div>
                @endif

                <div class="input-container">
                    <input type="text" class="animated-input" id="otp" name="otp" placeholder=" " maxlength="6" pattern="[0-9]{6}" required autofocus>
                    <label for="otp" class="animated-label">Enter OTP</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3" id="verifyBtn">
                    <i class="fas fa-check-circle"></i> Verify OTP
                </button>

                <div class="text-center mt-3">
                    <p style="font-size: 14px; color: #666;">Didn't receive the code?</p>
                    <a href="javascript:void(0);" id="resendLink" style="color: #007bff; text-decoration: none;">
                        <i class="fas fa-redo"></i> Resend OTP
                    </a>
                    <p id="resendTimer" style="font-size: 12px; color: #999; margin-top: 5px; display: none;">
                        Resend available in <span id="timer">60</span> seconds
                    </p>
                </div>

                <div class="text-center mt-3">
                    <a href="{{url('/')}}" style="color: #666; text-decoration: none; font-size: 14px;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let timerInterval;
            let timeLeft = 60;
            let canResend = false;

            // Start countdown timer
            function startTimer() {
                $('#resendTimer').show();
                $('#resendLink').css('pointer-events', 'none').css('opacity', '0.5');
                canResend = false;

                timerInterval = setInterval(function() {
                    timeLeft--;
                    $('#timer').text(timeLeft);

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        $('#resendTimer').hide();
                        $('#resendLink').css('pointer-events', 'auto').css('opacity', '1');
                        canResend = true;
                        timeLeft = 60;
                    }
                }, 1000);
            }

            // Start timer on page load
            startTimer();

            // OTP input - only allow numbers
            $('#otp').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Resend OTP
            $('#resendLink').on('click', function() {
                if (!canResend) {
                    return false;
                }

                $.ajax({
                    url: '{{ url("/resend-otp") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_id: $('input[name="user_id"]').val(),
                        email: $('input[name="email"]').val()
                    },
                    beforeSend: function() {
                        $('#resendLink').html('<i class="fas fa-spinner fa-spin"></i> Sending...');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('OTP has been resent to your email!');
                            timeLeft = 60;
                            startTimer();
                        } else {
                            alert(response.message || 'Failed to resend OTP. Please try again.');
                        }
                        $('#resendLink').html('<i class="fas fa-redo"></i> Resend OTP');
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                        $('#resendLink').html('<i class="fas fa-redo"></i> Resend OTP');
                    }
                });
            });

            // Form submission
            $('#otpForm').on('submit', function(e) {
                const otp = $('#otp').val();
                if (otp.length !== 6) {
                    e.preventDefault();
                    alert('Please enter a valid 6-digit OTP.');
                    return false;
                }
            });
        });
    </script>
</body>

</html>

