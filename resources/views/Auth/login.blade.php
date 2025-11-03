@php
if(isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
$email = $_COOKIE['email'];
$password = $_COOKIE['password'];
$remember = "checked='checked'";}
else{
$email = NULL;
$password = NULL;
$remember = NULL;}

@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('assets/css/login.css')}}">
    <link rel="icon" type="image/x-icon" href="{{asset('assets/images/favicon.webp')}}">

    <!-- Latest compiled and minified CSS bootstrap 5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>{{env('APP_NAME')}} | Login</title>
</head>

<body>
    <div class="container login-container">
        <div class="card login-card">
            <h3 class="card-heading">Sign in</h3>
            <p class="login-pg">Please login to continue to your account.</p>
            <form method="POST" action="{{url('login')}}">
                @csrf
                @if(Session::has('error'))
                <div class="alert alert-danger" id="errorMsg">
                    {{ Session::get('error') }}
                </div>
                @endif
                <div class="input-container">
                    <input type="text" class="animated-input" id="email" placeholder=" " name="email" value="{{@$email}}">
                    <label for="example" class="animated-label">Email</label>
                </div>

                <div class="input-container">
                    <input type="password" class="animated-input" id="password" placeholder=" " name="password" value="{{@$password}}">
                    <label for="example" class="animated-label">Password</label>
                    <i class="eye-icon fas fa-eye-slash" onclick="togglePasswordVisibility()"></i>
                </div>


                <div class="check-container">
                    <input class="form-check-input" type="checkbox" {{ @$remember ?? '' }} name="remember" value="something">
                    <span class="remember-text">Remember password</span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-block" style="background-color: #012670; color: white;">Sign in</button>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="{{url('/signup')}}" class="text-decoration-none" style="color: #012670; font-weight: 600;">Sign up here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.eye-icon');

            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }
    </script>


</body>

</html>