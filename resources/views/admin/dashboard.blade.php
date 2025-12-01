@extends('admin.master-main')
@section('content')
    @php
        $booking = App\Models\Booking::count();
        $customers = App\Models\Customer::count();
        $userId = Auth::user()->id;

        // Your personal revenue (investor login)
        $bookingIds = App\Models\BookingData::whereHas('vehicle.investor', fn($q) => $q->where('user_id', $userId))
            ->pluck('booking_id')->unique();
        $totalAmount = App\Models\BookingPaymentHistory::whereIn('booking_id', $bookingIds)->sum('paid_amount');

        $receiveable = App\Models\Payment::sum('pending_amount');
        $revenue = App\Models\BookingPaymentHistory::sum('paid_amount');

        // 1. Investor Chart - Kept exactly like before but optimized
        $investors = App\Models\Investor::withCount('vehicle')->get();

        $chartData = [
            'labels' => $investors->pluck('name')->toArray(),
            'vehicles' => $investors->pluck('vehicle_count')->toArray(),
            'revenue' => $investors->map(function ($investor) {
                return App\Models\BookingPaymentHistory::whereIn('booking_id', function ($q) use ($investor) {
                    $q->select('bd.booking_id')
                        ->from('booking_data as bd')
                        ->join('vehicles as v', 'bd.vehicle_id', '=', 'v.id')
                        ->where('v.investor_id', $investor->id);
                })->sum('paid_amount');
            })->toArray(),
        ];

        // 2. Vehicle SOA Chart (only your vehicles - safe)
        $vehicles2 = App\Models\Vehicle::with('bookingData')->get();

        $bookingPriceMap = [];
        $bookingIds = [];
        $bookingVehicleMap = [];
        $vehicleMap = []; // vehicle_id => plate_no (or identifier)

        // collect bookings and vehicle mapping
        foreach ($vehicles2 as $vehicle) {
            $vehicleMap[$vehicle->id] = $vehicle->number_plate ?? $vehicle->id;

            foreach ($vehicle->bookingData as $booking2) {
                $bid = $booking2->booking_id;
                $bookingPriceMap[$bid] = $booking2->price;
                $bookingIds[] = $bid;
                $bookingVehicleMap[$bid] = $vehicle->id;
            }
        }

        $paymentsPerBooking = [];
        if (!empty($bookingIds)) {
            $paymentsPerBooking = \App\Models\Payment::whereIn('booking_id', $bookingIds)
                ->selectRaw('booking_id, SUM(paid_amount) as paid_amount')
                ->groupBy('booking_id')
                ->pluck('paid_amount', 'booking_id')
                ->toArray();
        }

        $vehiclePaid = [];
        $vehicleUnpaid = [];

        foreach ($vehicleMap as $vid => $plate) {
            $vehiclePaid[$vid] = 0;
            $vehicleUnpaid[$vid] = 0;
        }

        // calculate paid/unpaid per vehicle
        foreach ($bookingPriceMap as $bookingId => $price) {
            $paid = $paymentsPerBooking[$bookingId] ?? 0;
            $vehicleId = $bookingVehicleMap[$bookingId];

            if ($paid >= $price) {
                $vehiclePaid[$vehicleId] += $price;
            } elseif ($paid > 0 && $paid < $price) {
                $vehiclePaid[$vehicleId] += $paid;
                $vehicleUnpaid[$vehicleId] += ($price - $paid);
            } else {
                $vehicleUnpaid[$vehicleId] += $price;
            }
        }

        // prepare chart data
        $labels = array_values($vehicleMap); // vehicle plate numbers
        $paidData = array_values($vehiclePaid);
        $unpaidData = array_values($vehicleUnpaid);

        $chartConfig1 = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Paid',
                        'data' => $paidData,
                        'backgroundColor' => 'green',
                    ],
                    [
                        'label' => 'Not Paid',
                        'data' => $unpaidData,
                        'backgroundColor' => 'red',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'top'],
                    'title' => [
                        'display' => true,
                        'text' => 'SOA Report - Rental Amount per Car',
                    ],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Rental Amount']],
                    'x' => ['title' => ['display' => true, 'text' => 'Car Plate No.']],
                ],
            ],
        ];

        $soaChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig1));


        // 3. ONE query for all customer + salesman charts
        $stats = DB::table('bookings as b')
            ->leftJoin('booking_data as bd', 'b.id', '=', 'bd.booking_id')
            ->leftJoin('invoices as i', 'b.id', '=', 'i.booking_id')
            ->leftJoin('payments as p', 'b.id', '=', 'p.booking_id')
            ->leftJoin('customers as c', 'b.customer_id', '=', 'c.id')
            ->leftJoin('sale_people as sp', 'b.sale_person_id', '=', 'sp.id')
            ->selectRaw('
                c.id as customer_id,
                COALESCE(c.customer_name, "Unknown") as customer_name,
                sp.id as sp_id,
                COALESCE(sp.name, "Unknown") as salesman_name,
                COALESCE(SUM(bd.price), 0) as total_sale,
                COALESCE(SUM(i.total_amount), 0) as total_invoice,
                COALESCE(SUM(p.paid_amount), 0) as total_paid
            ')
            ->groupBy('c.id', 'c.customer_name', 'sp.id', 'sp.name')
            ->get();

        // Customer Top 10 Sale
        $top10 = $stats->sortByDesc('total_sale')->take(10);
        $customerChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => 'bar',
            'data' => [
                'labels' => $top10->pluck('customer_name')->map(fn($n) => mb_strlen($n) > 15 ? mb_strimwidth($n, 0, 15, '...') : $n)->toArray(),
                'datasets' => [
                    ['label' => 'Total Sale', 'data' => $top10->pluck('total_sale')->toArray()],
                    ['label' => 'Paid', 'data' => $top10->pluck('total_paid')->toArray()]
                ]
            ]
        ], JSON_UNESCAPED_SLASHES));

        // Top 10 Receivable
        $rec = $stats->map(fn($r) => ['customer_name' => $r->customer_name, 'receivable' => $r->total_invoice - $r->total_paid])
            ->sortByDesc('receivable')->take(10);
        $receivableChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => 'bar',
            'data' => [
                'labels' => $rec->pluck('customer_name')->map(fn($n) => mb_strlen($n) > 15 ? mb_strimwidth($n, 0, 15, '...') : $n)->toArray(),
                'datasets' => [['label' => 'Receivable', 'data' => $rec->pluck('receivable')->toArray(), 'backgroundColor' => 'rgba(255,99,132,0.6)']]
            ]
        ], JSON_UNESCAPED_SLASHES));

        // Salesman Chart
        $salesman = $stats->groupBy('salesman_name')->map(fn($g) => [
            'name' => $g->first()->salesman_name,
            'invoice' => $g->sum('total_invoice'),
            'paid' => $g->sum('total_paid'),
            'rec' => $g->sum('total_invoice') - $g->sum('total_paid')
        ])->values();

        $salesmanChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => 'bar',
            'data' => [
                'labels' => $salesman->pluck('name')->toArray(),
                'datasets' => [
                    ['label' => 'Invoice', 'data' => $salesman->pluck('invoice')->toArray(), 'backgroundColor' => 'rgba(54,162,235,0.6)'],
                    ['label' => 'Paid', 'data' => $salesman->pluck('paid')->toArray(), 'backgroundColor' => 'rgba(75,192,192,0.6)'],
                    ['label' => 'Receivable', 'data' => $salesman->pluck('rec')->toArray(), 'backgroundColor' => 'rgba(255,99,132,0.6)']
                ]
            ]
        ]));
    @endphp
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="row ">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Total Booking</h5>
                                            <h2 class="mb-3 font-18">{{ $booking }}</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="{{ asset('assets/img/banner/1.png') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15"> Customers</h5>
                                            <h2 class="mb-3 font-18">{{ $customers }}</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="{{ asset('assets/img/banner/2.png') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (!Auth::user()->hasRole('investor'))
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row ">
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                            <div class="card-content">
                                                <h5 class="font-15">Receivable</h5>
                                                <h2 class="mb-3 font-18">AED {{ number_format($receiveable, 0) }}</h2>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                            <div class="banner-img">
                                                <img src="{{ asset('assets/img/banner/3.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row ">
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                            <div class="card-content">
                                                <h5 class="font-15">Vehicles</h5>
                                                <h2 class="mb-3 font-18">{{ $vehicles }}</h2>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                            <div class="banner-img">
                                                <img src="{{ asset('assets/img/banner/vehicle.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Revenue</h5>
                                            <h2 class="mb-3 font-18">
                                                @if (Auth::user()->getRoleNames()->first() == 'investor')
                                                        AED {{ number_format($totalAmount, 0) }}
                                                    </h2>
                                                @else
                                                AED {{ number_format($revenue, 0) }}</h2>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="{{ asset('assets/img/banner/4.png') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php
                // Collect investors, vehicles, and revenue data
                $labels = [];
                $vehicles = [];
                $revenues = [];

                foreach ($investors as $item) {
                    $labels[] = $item->name;

                    $investorID = $item->id;
                    $bookingIds = App\Models\BookingData::whereHas('vehicle.investor', function ($q) use ($investorID) {
                        $q->where('id', $investorID);
                    })->pluck('booking_id')->unique();

                    $totalAmount = App\Models\BookingPaymentHistory::whereIn('booking_id', $bookingIds)->sum('paid_amount');

                    $vehicles[] = $item->vehicle->count();
                    $revenues[] = $totalAmount;
                }

                // Build ONE chart config
                $chartConfig = [
                    'type' => 'bar',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Total Vehicles',
                                'data' => $vehicles,
                                'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                            ],
                            [
                                'label' => 'Revenue (AED)',
                                'data' => $revenues,
                                'backgroundColor' => 'rgba(255, 206, 86, 0.6)',
                            ],
                        ],
                    ],
                    'options' => [
                        'responsive' => true,
                        'plugins' => [
                            'legend' => ['position' => 'top'],
                            'title' => ['display' => true, 'text' => 'Investor Performance (All Investors)'],
                        ],
                    ],
                ];

                $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));



            @endphp

            @if (!Auth::user()->hasRole('investor'))
                {{-- <div class="row">
                    <div class="col-12 text-center">
                        <img src="{{ $chartUrl }}" alt="All Investors Chart" class="img-fluid">
                    </div>
                </div> --}}
                <div class="row">

                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pr-0 pt-3 text-center">



                                            <!-- Chart -->
                                            <h4 class="card-title mb-3">Investor Vehicle Revenue</h4>
                                            <div class="card-content">
                                                <img src="{{ $chartUrl }}" alt="All Investors Chart" class="img-fluid">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row">

                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pr-0 pt-3 text-center">
                                            <h4 class="card-title mb-3">SOA</h4>
                                            <div class="card-content">
                                                <img src="{{ $soaChartUrl }}" alt="All Investors Chart" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pr-0 pt-3 text-center">
                                            <h4 class="card-title mb-3">Customer Sales (Top 10)</h4>
                                            <div class="card-content">
                                                <img src="{{ $customerChartUrl }}" alt="All Investors Chart" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row">

                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pr-0 pt-3 text-center">
                                            <h4 class="card-title mb-3">Customer Receivable (Top 10)</h4>
                                            <div class="card-content">
                                                <img src="{{ $receivableChartUrl }}" alt="All Investors Chart"
                                                    class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card">
                            <div class="card-statistic-4">
                                <div class="align-items-center justify-content-between">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pr-0 pt-3 text-center">
                                            <h4 class="card-title mb-3">Salesman</h4>
                                            <div class="card-content">
                                                <img src="{{ $salesmanChartUrl }}" alt="All Investors Chart" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif





        </section>
    </div>


@endsection