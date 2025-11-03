@php

$name = ucfirst(strtolower(Auth::user()->first_name))." ".ucfirst(strtolower(Auth::user()->last_name));
$avatar = Avatar::create($name)->toBase64();

@endphp
<div class="appbar-container">
    <!-- navbar -->
    <div class="navbar-container">
        <div class="navbar-toggler-container">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="color: black;">
                <span class="navbar-toggler-icon"><img src="{{ asset('assets/images/hamburgur.svg') }}"></span>
            </button>
        </div>
        <div>
            <ul class="nav-ul">
                <!-- Admin dropdown -->
                <li class="nav-item dropdown" style="padding: 16px 10px; position: relative;">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="admin-icon" src="{{ $avatar }}" width="50" alt="Profile Picture">
                        <span class="admin">{{ $name }}</span>
                        <span class="dropdown-arrow">▼</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="background-color: white;">
                    <li><a class="dropdown-item" href="{{ url('profile') }}">Profile Settings</a></li>
                    <li><a class="dropdown-item" href="{{ url('logout') }}">Logout</a></li>
                    </ul>
                </li>
                <!-- Logout button -->
                <!-- <li class="nav-item" style="padding: 16px 40px;">
                    <a class="nav-link" href="{{ url('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: black;">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ url('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li> -->
            </ul>
        </div>
    </div>
</div>