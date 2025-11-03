<div class="sidebar-container-for-mobile-view">
    <div>
        <div class="logo-container"> <img class="logo" src="{{asset('assets/images/logo.png')}}" width="50px" height="50px"></div>
        <div>
            <ul class="ul-container">
                <li class="list-item {{(Request::path() == 'dashboard')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'dashboard')?'active-li':''}}" href="{{url('dashboard')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'dashboard')?'assets/images/dashboard-active-icon.svg':'assets/images/dashboard-icon.svg')}}" alt="error">Dashboard</a>
                </li>
                @if(auth()->user()->hasPermission('application','view'))
                <li class="list-item {{(Request::path() == 'application')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'application')?'active-li':''}}" href="{{url('application')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'application')?'assets/images/application-active.svg':'assets/images/application-icon.svg')}}" alt="error">Application
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('settlement','view'))
                <li class="list-item {{(Request::path() == 'settlement')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'settlement')?'active-li':''}}" href="{{url('settlement')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'settlement')?'assets/images/settlement-active-icon.svg':'assets/images/settlement-icon.svg')}}" alt="error">Settlements
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('dsa-code','view'))
                <li class="list-item {{(Request::path() == 'dsa-code')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'dsa-code')?'active-li':''}}" href="{{url('dsa-code')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'dsa-code')?'assets/images/dashboard-active-icon.svg':'assets/images/dashboard-icon.svg')}}" alt="error">DSA Code</a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('bank-target','view'))
                <li class="list-item {{(Request::path() == 'bank-target')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'bank-target')?'active-li':''}}" href="{{url('bank-target')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'bank-target')?'assets/images/dashboard-active-icon.svg':'assets/images/dashboard-icon.svg')}}" alt="error">Bank Target</a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('bank','view') )
                <li class="nav-item dropdown list-item {{(Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/user-icon.svg')}}" alt="error">Bank
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'show':''}}">

                        <li class="dropdown-list-li {{(Request::path() == 'bank')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'bank')?'active-li':''}}" href="{{url('bank')}}">All Bank</a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'bank/view/product')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'bank/view/product')?'active-li':''}}" href="{{url('bank/view/product')}}">Bank Product</a>
                        </li>

                    </ul>
                </li>
                @endif
                @if(auth()->user()->hasPermission('channel','view') ||auth()->user()->hasPermission('sales-person','view') )

                <li class="nav-item dropdown list-item {{(Request::path() == 'channel' ||Request::path() == 'sales-person')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/user-icon.svg')}}" alt="error">Users
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'channel' ||Request::path() == 'sales-person')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'channel' ||Request::path() == 'sales-person')?'show':''}}">
                        @if(auth()->user()->hasPermission('channel','view') )

                        <li class="dropdown-list-li {{(Request::path() == 'channel')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'channel')?'active-li':''}}" href="{{url('channel')}}">Channel Partner</a>
                        </li>
                        @endif

                        @if(auth()->user()->hasPermission('sales-person','view') )

                        <li class="dropdown-list-li {{(Request::path() == 'sales-person')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'sales-person')?'active-li':''}}" href="{{url('sales-person')}}">Sales Person</a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                @if(auth()->user()->hasPermission('staff','view'))
                <li class="nav-item dropdown list-item {{(Request::path() == 'staff' ||Request::path() == 'staff/view/role'||Request::path() =='staff/view/permissions')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/user-icon.svg')}}" alt="error">Staff
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'staff' ||Request::path() == 'staff/view/role'||Request::path() =='permissions')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'staff' ||Request::path() == 'staff/view/role'||Request::path() =='staff/view/permissions')?'show':''}}">
                        <li class="dropdown-list-li {{(Request::path() == 'staff')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'staff')?'active-li':''}}" href="{{url('staff')}}">Manage Staff</a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'staff/view/role')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'staff/view/role')?'active-li':''}}" href="{{url('staff/view/role')}}">Roles</a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'staff/view/permissions')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'staff/view/permissions')?'active-li':''}}" href="{{url('staff/view/permissions')}}">Permissions</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(auth()->user()->hasPermission('services','view'))
                <li class="nav-item dropdown list-item {{(Request::path() == 'services')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/user-icon.svg')}}" alt="error">Setting
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'services')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'services')?'show':''}}">
                        <li class="dropdown-list-li {{(Request::path() == 'services')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'services')?'active-li':''}}" href="{{url('services')}}">Services</a>
                        </li>
                    </ul>
                </li>
                @endif

            </ul>
        </div>
    </div>
    <div>
        <button class="log-out-btn" onclick="window.location.href=`{{url('logout')}}`">
            <img class="dashboard-icons" src="{{asset('assets/images/logout-icon.svg')}}">Log out
        </button>
    </div>
</div>