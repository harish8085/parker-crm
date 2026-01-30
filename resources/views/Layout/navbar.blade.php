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
                <!-- Notifications dropdown -->
                <li class="nav-item dropdown" style="padding: 16px 10px; position: relative;">
                    <a class="nav-link notification-icon" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative; text-decoration: none; padding: 8px 12px;">
                        <i class="fas fa-bell" style="font-size: 20px; color: #333;"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none; position: absolute; top: 5px; right: 5px; background-color: #dc3545; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold; line-height: 1;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 350px; max-width: 400px; max-height: 500px; overflow-y: auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <li>
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="background-color: #f8f9fa;">
                                <h6 class="mb-0" style="font-weight: 600;">Notifications</h6>
                                <button class="btn btn-sm btn-link text-primary p-0" id="markAllReadBtn" style="font-size: 12px; text-decoration: none;">Mark all as read</button>
                            </div>
                        </li>
                        <li>
                            <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center p-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="text-center p-2 border-top">
                                <a href="#" id="viewMoreNotifications" class="text-primary" style="text-decoration: none; font-size: 14px;">View More</a>
                            </div>
                        </li>
                    </ul>
                </li>
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