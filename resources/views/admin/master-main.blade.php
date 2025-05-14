<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))</title>
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/jquery-selectric/selectric.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel='shortcut icon' type='image/x-icon' href="{{ asset('assets/img/favicon.ico') }}" />
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn">
                                <i data-feather="align-justify"></i></a></li>
                        <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                                <i data-feather="maximize"></i>
                            </a></li>
                        <li>
                            <form class="form-inline mr-auto">
                                <div class="search-element">
                                    <input class="form-control" type="search" placeholder="Search" aria-label="Search"
                                        data-width="200">
                                    <button class="btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">

                    <li class="dropdown"><a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image"
                                src="{{ asset('assets/img/user.png') }}" class="user-img-radious-style"> <span
                                class="d-sm-none d-lg-inline-block"></span></a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">{{ Auth::user()->name }}</div>
                            <a href="#" class="dropdown-item has-icon"> <i
                                    class="far
										fa-user"></i> Profile
                            </a> <a href="#" class="dropdown-item has-icon"> <i class="fas fa-bolt"></i>
                                Activities
                            </a> <a href="#" class="dropdown-item has-icon"> <i class="fas fa-cog"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item has-icon text-danger" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        {{-- <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('booker.dashboard') }}"> <img alt="image" src="{{ asset('assets/img/logo.png') }}" --}}
                        <a href="{{ role_base_route('dashboard') }}"> <img alt="image" src="{{ asset('assets/img/logo.png') }}"
                                class="header-logo" /> <span class="logo-name">Rapid Digi</span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Main</li>
                        <li class="dropdown">
                            <a href="{{ role_base_route('dashboard') }}" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        @can('manage customers')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="user"></i><span>Customer</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ auth()->user()->hasRole('admin') ? route('admin.customer.create') : route('booker.customer.create') }}">Add Customer</a></li>
                                <li><a class="nav-link" href="{{ auth()->user()->hasRole('admin') ? route('admin.customer.index') : route('booker.customer.index') }}">Customer list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('manage investors')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                data-feather="dollar-sign"></i><span>Investor</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('admin.investor.create') }}">Add Investor</a></li>
                                <li><a class="nav-link" href="{{ route('admin.investor.index') }}">Investor List</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('manage bookers')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="user-check"></i><span>Booking user</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('admin.booker.create') }}">Add Booking User</a></li>
                                <li><a class="nav-link" href="{{ route('admin.booker.index') }}">Booking User list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('manage vehicles')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="truck"></i><span>Vehicle</span></a>
                            <ul class="dropdown-menu">
                                <li style="display: none;"><a class="nav-link" href="{{ route('admin.vehicle-type.create') }}">Add Vehicle Type</a></li>
                                <li style="display: none;"><a class="nav-link" href="{{ route('admin.vehicle-type.index') }}">Vehicle Type list</a></li>
                                <li><a class="nav-link" href="{{ route('admin.vehicle.create') }}">Add Vehicle</a></li>
                                <li><a class="nav-link" href="{{ route('admin.vehicle.index') }}">Vehicle list</a></li>
                                <li><a class="nav-link" href="{{ route('admin.vehicle-status.create') }}">Add Vehicle Status</a></li>
                                <li><a class="nav-link" href="{{ route('admin.vehicle-status.index') }}">Vehicle Status list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('manage sale person')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i class="fas fa-user-tie" style="width: 16px; margin-right: 8px; text-align: left;"></i>
                                <span>Sale Person</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('admin.sale-person.create') }}">Add Sale Person</a></li>
                                <li><a class="nav-link" href="{{ route('admin.sale-person.index') }}">Sale Person list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('manage booking')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>Booking Vehicle</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ role_base_route('customer-booking.create') }}">Add Booking</a></li>
                                <li><a class="nav-link" href="{{ role_base_route('customer-booking.index') }}">Booking list</a></li>
                                <li><a class="nav-link" href="{{ role_base_route('status-form') }}">Assign Status</a></li>
                                <li><a class="nav-link" href="{{ role_base_route('assined-vehicle') }}">Assined Vehicles</a></li>
                            </ul>
                        </li>
                        @endcan

                    </ul>
                </aside>
            </div>





            @yield('content')












            <footer class="main-footer">
                <div class="footer-left">
                    <a href="">Rapid Digi Rental System</a></a>
                </div>
                <div class="footer-right">
                </div>
            </footer>
        </div>
    </div>
    <!-- General JS Scripts -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
    <!-- JS Libraies -->
    <script src="{{ asset('assets/js/page/index.js') }}"></script>
    <script src="{{ asset('assets/bundles/cleave-js/dist/cleave.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/cleave-js/dist/addons/cleave-phone.us.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-selectric/jquery.selectric.min.js') }}"></script>
    <!-- Page Specific JS File -->
    <script src="{{ asset('assets/js/page/forms-advanced-forms.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <!-- Template JS File -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <!-- Custom JS File -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <!-- Custom Ajax File -->
    <script src="{{ asset('assets/js/ajax.js') }}"></script>
    <!-- Sweet Alert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('script')

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `{{ session('error') }}`.replace(/\n/g, '\n'),
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            });
        </script>
    @endif


</body>

</html>
