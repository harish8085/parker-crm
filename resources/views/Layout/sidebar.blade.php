<div class="sidebar-container">
    <div>
        <div class="logo-container">
            <a href="/">

                <img class="logo" src="{{asset('assets/images/logo.png')}}" width="50px" height="50px">
            </a>
        </div>

        <div>
            <ul class="ul-container">
                <li class="list-item {{(Request::path() == 'dashboard')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'dashboard')?'active-li':''}}" href="{{url('dashboard')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'dashboard')?'assets/images/home-active.svg':'assets/images/home.svg')}}" alt="error">Dashboard</a>
                </li>

                <li class="list-item {{(Request::path() == 'announcements')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'announcements')?'active-li':''}}" href="{{url('announcements')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'announcements')?'assets/images/announcement-active.svg':'assets/images/announcement.svg')}}" alt="error">Announcement
                    </a>
                </li>

                @if(auth()->user()->hasPermission('application','view'))
                <li class="list-item {{(Request::path() == 'application')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'application')?'active-li':''}}" href="{{url('application')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'application')?'assets/images/application-active.svg':'assets/images/application.svg')}}" alt="error">Application
                    </a>
                </li>
                @endif
                @if(auth()->user()->roles[0]->id ==1)
                <li class="list-item {{(Request::path() == 'announcement-categories')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'announcement-categories')?'active-li':''}}" href="{{url('announcement-categories')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'announcement-categories')?'assets/images/announcement-categories-active.svg':'assets/images/announcement-categories.svg')}}" alt="error">Announcement Categories
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('bank_mis','view'))
                <li class="list-item {{(Request::path() == 'bank_mis')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'bank_mis')?'active-li':''}}" href="{{url('bank_mis')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'bank_mis')?'assets/images/application-active.svg':'assets/images/application.svg')}}" alt="error">Bank MIS</a>
                </li>
                @endif

                @if(auth()->user()->roles[0]->id ==1)
                <li class="list-item {{(Request::path() == 'advance')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'advance')?'active-li':''}}" href="{{url('advance')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'advance')?'assets/images/sheet-active.svg':'assets/images/sheet.svg')}}" alt="error">Advance
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('settlement','view'))
                <li class="list-item {{(Request::path() == 'settlement')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'settlement')?'active-li':''}}" href="{{url('settlement')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'settlement')?'assets/images/settlement-active.svg':'assets/images/settlement.svg')}}" alt="error">Settlements
                    </a>
                </li>
                @endif


                @if(auth()->user()->hasPermission('settlement','view') || in_array(auth()->user()->roles[0]->id, [2, 3]))
                <li class="list-item {{(Request::path() == 'transactions')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'transactions')?'active-li':''}}" href="{{url('transactions')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'transactions')?'assets/images/settlement-active.svg':'assets/images/settlement.svg')}}" alt="error">Transactions
                    </a>
                </li>
                @endif
                <!-- @if(auth()->user()->hasPermission('invoice','view'))
                <li class="list-item {{(Request::path() == 'invoice')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'invoice')?'active-li':''}}" href="{{url('invoice')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'invoice')?'assets/images/invoice_blue.svg':'assets/images/invoice_white.svg')}}" alt="error">Invoices
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('mis_tracker','view'))
                <li class="list-item {{(Request::path() == 'mis_tracker')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'mis_tracker')?'active-li':''}}" href="{{url('mis_tracker')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'mis_tracker')?'assets/images/mis-tracker_blue.svg':'assets/images/mis-tracker-white.svg')}}" alt="error"> MIS Tracker
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('invoice_payment','view'))
                <li class="list-item {{(Request::path() == 'invoice_payment')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'invoice_payment')?'active-li':''}}" href="{{url('invoice_payment')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'invoice_payment')?'assets/images/invoice_payment-blue.svg':'assets/images/invoice_payment-white.svg')}}" alt="error"> Invoice Payment
                    </a>
                </li>
                @endif -->
                @if(auth()->user()->hasPermission('dsa-code','view'))
                <li class="list-item {{(Request::path() == 'dsa-code')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'dsa-code')?'active-li':''}}" href="{{url('dsa-code')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'dsa-code')?'assets/images/dsa-code-active.svg':'assets/images/dsa-code.svg')}}" alt="error">Bank Code</a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('bank-target','view'))
                <li class="list-item {{(Request::path() == 'bank-target')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'bank-target')?'active-li':''}}" href="{{url('bank-target')}}">
                        <img class="dashboard-icons" src="{{ asset((Request::path() == 'bank-target')?'assets/images/target-active.svg':'assets/images/target.svg')}}" alt="error">Bank Target</a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('sample-sheet','view'))
                <li class="list-item {{(Request::path() == 'sample-sheet')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'sample-sheet')?'active-li':''}}" href="{{url('sample-sheet')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'sample-sheet')?'assets\images\sample-sheet-active.svg':'assets\images\sample-sheet.svg')}}" alt="error">Sample Sheet
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('sheet-matching','view'))
                <li class="list-item {{(Request::path() == 'sheet-matching')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'sheet-matching')?'active-li':''}}" href="{{url('sheet-matching')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'sheet-matching')?'assets/images/sheet-active.svg':'assets/images/sheet.svg')}}" alt="error">Sheet Data
                    </a>
                </li>
                @endif

                @if(auth()->user()->hasPermission('bank','view') )
                <li class="nav-item dropdown list-item {{(Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/bank.svg')}}" alt="error">Bank
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'bank' ||Request::path() == 'bank/view/product')?'show':''}}">

                        <li class="dropdown-list-li {{(Request::path() == 'bank')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'bank')?'active-li':''}}" href="{{url('bank')}}">All Bank</a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'product')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'product')?'active-li':''}}" href="{{url('product')}}">All Product</a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'bank/view/product')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'bank/view/product')?'active-li':''}}" href="{{url('bank/view/product')}}">Bank Product</a>
                        </li>

                    </ul>
                </li>
                @endif


                @if(auth()->user()->hasPermission('invoice','view') )
                <li class="nav-item dropdown list-item {{(Request::path() == 'invoice' ||Request::path() == '')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/mis-tracker-white.svg')}}" alt="error">Invoice
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'invoice' ||Request::path() == 'invoice')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'invoice' ||Request::path() == '')?'show':''}}">

                        <li class="dropdown-list-li {{(Request::path() == 'invoice')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'invoice')?'active-li':''}}" href="{{url('invoice')}}">
                                <img class="dashboard-icons" src="{{asset((Request::path() == 'invoice')?'assets/images/invoice_blue.svg':'assets/images/invoice_white.svg')}}" alt="error">Invoices
                            </a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'invoice_payment')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'invoice_payment')?'active-li':''}}" href="{{url('invoice_payment')}}">
                                <img class="dashboard-icons" src="{{asset((Request::path() == 'invoice_payment')?'assets/images/invoice_payment-blue.svg':'assets/images/invoice_payment-white.svg')}}" alt="error"> Invoice Payment
                            </a>
                        </li>
                        <li class="dropdown-list-li {{(Request::path() == 'mis_tracker')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'mis_tracker')?'active-li':''}}" href="{{url('mis_tracker')}}">
                                <img class="dashboard-icons" src="{{asset((Request::path() == 'mis_tracker')?'assets/images/mis-tracker_blue.svg':'assets/images/mis-tracker-white.svg')}}" alt="error"> MIS Tracker
                            </a>
                        </li>
                    </ul>
                </li>
                @endif


                @if(auth()->user()->hasPermission('master_code','view') || auth()->user()->roles[0]->name ==2)

                <li class="list-item {{(Request::path() == 'master-code')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'master-code')?'active-li':''}}" href="{{url('master-code')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'master-code')?'assets/images/master-code-active.svg':'assets/images/master-code.svg')}}" alt="error">Invite User
                    </a>
                </li>
                @endif

                @if(auth()->user()->roles[0]->id ==2 || auth()->user()->roles[0]->id ==6)

                <li class="list-item {{(Request::path() == 'link-bank')?'active-li':''}}">
                    <a class="nav-links {{(Request::path() == 'link-bank')?'active-li':''}}" href="{{url('link-bank')}}">
                        <img class="dashboard-icons" src="{{asset((Request::path() == 'master-code')?'assets/images/bank-active.svg':'assets/images/bank.svg')}}" alt="error">Link Bank
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('channel','view') ||auth()->user()->hasPermission('sales-person','view') )

                <li class="nav-item dropdown list-item {{(Request::path() == 'channel' ||Request::path() == 'sales-person')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/users.svg')}}" alt="error">Users
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
                        @if(auth()->user()->roles[0]->id == 1)
                        <li class="dropdown-list-li {{(Request::path() == 'maker-checker')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'maker-checker')?'active-li':''}}" href="{{url('maker-checker')}}">Maker / Checker</a>
                        </li>
                        @endif

                        <!-- @if(auth()->user()->hasPermission('sales-person','view') )

                        <li class="dropdown-list-li {{(Request::path() == 'sales-person')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'sales-person')?'active-li':''}}" href="{{url('sales-person')}}">Sales Person</a>
                        </li>
                        @endif -->

                    </ul>
                </li>
                @endif

                @if(auth()->user()->hasPermission('staff','view'))
                <li class="nav-item dropdown list-item {{(Request::path() == 'staff' ||Request::path() == 'staff/view/role'||Request::path() =='staff/view/permissions')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/staff.svg')}}" alt="error">Staff
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
                @if(auth()->user()->hasPermission('services','view') ||auth()->user()->hasPermission('bank-payout','view') )
                <li class="nav-item dropdown list-item {{(Request::path() == 'services' || Request::path() == 'bank-payout')?'active':''}}">
                    <a class="nav-link dropdown-toggle nav-links" role="button" data-bs-toggle="dropdown">
                        <img class="dashboard-icons" src="{{asset('assets/images/setting.svg')}}" alt="error">Setting
                        <span class="custom-dropdown-arrow">
                            <img class="dropdown-icon" src="{{asset((Request::path() == 'services' || Request::path() == 'bank-payout' || Request::path() == 'remark-status')?'assets/images/arrow-dropdown.svg':'assets/images/close-dropdown-sidebar-icon.svg')}}" alt="arrow">
                        </span>
                    </a>
                    <ul class="dropdown-menu sidebar-menu {{(Request::path() == 'services' || Request::path() == 'bank-payout')?'show':''}}">
                        <li class="dropdown-list-li {{(Request::path() == 'services')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'services')?'active-li':''}}" href="{{url('services')}}">Services</a>
                        </li>
                        @if(auth()->user()->roles[0]->id ==1)
                        <li class="dropdown-list-li {{(Request::path() == 'bank-payout')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'bank-payout')?'active-li':''}}" href="{{url('bank-payout')}}">Bank Payout</a>
                        </li>
                        @endif
                        @if(auth()->user()->roles[0]->id ==1)
                        <li class="dropdown-list-li {{(Request::path() == 'remark-status')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'remark-status')?'active-li':''}}" href="{{url('remark-status')}}">Remark Status</a>
                        </li>
                        @endif
                        @if(auth()->user()->roles[0]->id ==1 || auth()->user()->roles[0]->id ==2)
                        <li class="dropdown-list-li {{(Request::path() == 'master-data')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'master-data')?'active-li':''}}" href="{{url('master-data')}}">Master Data</a>
                        </li>
                        @endif
                        @if(auth()->user()->roles[0]->id ==1 || auth()->user()->roles[0]->id ==2)
                        <li class="dropdown-list-li {{(Request::path() == 'master-code')?'active-li':''}}">
                            <a class="dropdown-item nav-links {{(Request::path() == 'master-code')?'active-li':''}}" href="{{url('master-code')}}">Master Code</a>
                        </li>
                        @endif

                    </ul>
                </li>

                @endif

            </ul>
        </div>
    </div>
    <!-- <div>
        <button class="log-out-btn" onclick="window.location.href=`{{url('logout')}}`">
            <img class="dashboard-icons" src="{{asset('assets/images/logout-icon.svg')}}">Log out
        </button>
    </div> -->
</div>