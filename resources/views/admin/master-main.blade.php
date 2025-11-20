@php $userRole= Auth::user()->getRoleNames()->first(); @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))</title>
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.css') }}">

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>
        function playAudio() {
            var audio = new Audio("{{ asset('notificationbell.mp3') }}");
            audio.play();
        }
        Pusher.logToConsole = false;

        var pusher = new Pusher('ce96edbc440e6ff972a0', {
            cluster: 'us3'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function(data) {
            var investorID = {
                {
                    Auth::user() - > id
                }
            };
            if (investorID == data.investorId) {
                console.log(data);
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 50000,
                    extendedTimeOut: 50000
                };
                toastr.success(data.message);
                playAudio();

                // 🔔 Update badge count (optional, you can re-fetch from DB via AJAX)
                let badge = $('#notification-count');
                let current = parseInt(badge.text()) || 0;
                badge.text(current + 1).show();
            }

        });
    </script>


    <!-- Bootstrap Pagination CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/jquery-selectric/selectric.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn">
                                <i data-feather="align-justify"></i></a>
                        </li>
                        <li>
                            <a href="#" class="nav-link nav-link-lg fullscreen-btn">
                                <i data-feather="maximize"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown position-relative">
                            <a href="#" id="notification-icon" class="nav-link nav-link-lg dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell" style="font-size: 20px; color: rgb(110, 110, 110);"></i>
                                @php
                                $unreadCount = App\Models\Notification::where('user_id', Auth::user()->id)
                                ->where('is_read', 0)
                                ->count();
                                @endphp
                                @if($unreadCount > 0)
                                <span class="badge badge-warning badge-counter"
                                    id="notification-count"
                                    style="position: absolute; top: 5px; right: 5px; font-size: 10px;">
                                    {{ $unreadCount }}
                                </span>
                                @endif
                            </a>

                            <div class="dropdown-menu"
                                id="notification-dropdown"
                                style="left: 0; right: auto; transform: translateX(0); min-width: 300px; max-height: 300px; overflow-y: auto; font-size:13px;"
                                aria-labelledby="notification-icon">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div id="notification-items">
                                    @if (auth()->user()->hasRole('investor'))
                                    @php
                                    $notifications= App\Models\Notification::where('user_id', Auth::user()->id)
                                    ->where('role', 'investor')
                                    ->get();
                                    @endphp
                                    @foreach ($notifications as $item)
                                    <span class="dropdown-item text-muted" style="white-space: normal; word-wrap: break-word;">
                                        {{ $item->vehicle->vehicle_name ?? $item->vehicle->temp_vehicle_detail }}
                                        | {{ $item->message }}
                                    </span>
                                    @endforeach
                                    @else
                                    @php
                                    $notifications= App\Models\Notification::where('role', Auth::user()->getRoleNames()->first())->get();
                                    @endphp
                                    @foreach ($notifications as $item)
                                    <a href="{{ url('customer-booking/') }}" class="dropdown-item text-muted" style="white-space: normal; word-wrap: break-word;">
                                        {{ $item->message }}
                                    </a>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
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
                        <a href="{{ route('dashboard') }}"> <img alt="image" style="height: 100%; width: 70%;" src="{{ asset('assets/img/logo.png') }}"
                                class="header-logo" /> <span class="logo-name"></span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Main</li>
                        <li class="dropdown">
                            <a href="{{ route('dashboard') }}" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        @can('view customer')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="user"></i><span>Customer</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('customer.index') }}">Customer list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('view investor')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="dollar-sign"></i><span>Investor</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('investor.create') }}">Add Investor</a></li>
                                <li><a class="nav-link" href="{{ route('investor.index') }}">Investor List</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('view booker')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="user-check"></i><span>Booking user</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('booker.create') }}">Add Booking User</a></li>
                                <li><a class="nav-link" href="{{ route('booker.index') }}">Booking User list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('view vehicle')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="truck"></i><span>Vehicle</span></a>
                            <ul class="dropdown-menu">
                                <li style="display: none;"><a class="nav-link" href="{{ route('vehicle-type.index') }}">Vehicle Type list</a></li>
                                <li><a class="nav-link" href="{{ route('vehicle.index') }}">Vehicle list</a></li>
                                <li><a class="nav-link" href="{{ route('vehicle-status.index') }}">Vehicle Status list</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('view sale men')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i class="fas fa-user-tie" style="width: 16px; margin-right: 8px; text-align: left;"></i>
                                <span>Sale Person</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('sale-person.index') }}">Sale Person list</a></li>
                            </ul>
                        </li>
                        @endcan

                        @can('view bank')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i class="fa-landmark" style="width: 16px; margin-right: 8px; text-align: left;"></i>
                                <span>Banks</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('bank.index') }}">Bank list</a></li>
                            </ul>
                        </li>
                        @endcan

                        @can('view booking')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>Booking Vehicle</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('get.invoice') }}">Invoice list</a></li>
                                <li><a class="nav-link" href="{{ route('customer-booking.index') }}">Booking list</a></li>
                                @can('vehicle with status')
                                <li><a class="nav-link" href="{{ route('assined.vehicle') }}">Assigned Vehicles</a></li>
                                @endcan
                                @can('view booking')
                                <li><a class="nav-link" href="{{ route('invoice-type.index') }}">Invoice type</a></li>
                                @endcan

                            </ul>
                        </li>
                        @endcan

                        @can('view payment')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="file-text"></i><span>Payments</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('payment.index') }}">Payment list</a></li>
                                <li><a class="nav-link" href="{{ route('credit-note.index') }}">Credit Note list</a></li>
                            </ul>
                        </li>
                        @endcan

                        @can('view reports')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>Reports</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('soaReport') }}">SOA Report</a></li>
                                <li><a class="nav-link" href="{{ route('customerWiseReport') }}">Customer Wise Sales Report</a></li>
                                <li><a class="nav-link" href="{{ route('customerWiseReceivable') }}">Customer Wise Receivable</a></li>
                                <li><a class="nav-link" href="{{ route('salemenWiseReport') }}">Salemen Wise Report</a></li>
                            </ul>
                        </li>
                        @endcan

                        @can('view investor reports')
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>Report</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('bookingReport') }}">Vehicles & Revenue Booking</a></li>
                            </ul>
                        </li>
                        @endcan


                        @if (Auth::check() && Auth::user()->hasRole('admin'))
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>User Permissions</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="{{ route('view.role') }}">Roles</a></li>
                            </ul>
                        </li>
                        @endif

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

    <script>
        window.USER_ROLE = "{{ Auth::user()->getRoleNames()->first() }}";
    </script>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <!-- Sweet Alert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Custom jQuery Files -->
    <script src="{{ asset('assets/js/ajax-operations.js') }}"></script>
    <script src="{{ asset('assets/js/custom-ajax.js') }}"></script>
    <script src="{{ asset('assets/js/reports.js') }}"></script>
    <script src="{{ asset('assets/js/toastr.js') }}"></script>

    @yield('script')

    <script>
        $('#notification-icon').on('click', function() {
            $.ajax({
                url: '{{ route("mark-notifications-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#notification-count').hide(); // badge hide after click
                }
            });
        });
    </script>

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
            text: '{{ session('
            success ') }}',
        });
    </script>
    @endif

</body>

</html>