@extends('admin.master-main')
@section('content')
@php

    // $revenue= App\Models\BookingData::with('vehicle', 'invoice')
    //         ->whereHas('invoice', function($q){
    //             $q->where('invoice_status', 'sent');
    //         })->sum('price');

    // $totalAmount= App\Models\BookingData::with('vehicle', 'invoice')
    //         ->whereHas('invoice', function($q){
    //              $q->where('invoice_status', 'sent');
    //         })
    //         ->whereHas('vehicle.investor', function($q){
    //              $q->where('user_id', Auth::user()->id);
    //         })
    //         ->whereIn('transaction_type', ['1', '2'])->sum('item_total');



    /* Get total Bookings and customers */
    $booking= App\Models\Booking::count();
    $customers= App\Models\Customer::count();

    /* Get Investor Wise Revenue */
    $userId = Auth::user()->id;
    $bookingIds = App\Models\BookingData::whereHas('vehicle.investor', function ($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->pluck('booking_id')->unique();
    $totalAmount = App\Models\BookingPaymentHistory::whereIn('booking_id', $bookingIds)->sum('paid_amount');


    /* Get Total Revenue for Booker and Admin */
    $receiveable= App\Models\Payment::sum('pending_amount');

    /* Get Total Revenue without Receivable for Booker and Admin */
    $revenue= App\Models\BookingPaymentHistory::sum('paid_amount');

    /* Get Investor Total vehicle */
 $vehicles = App\Models\Vehicle::with(['investor', 'bookingData'])
    ->whereHas('investor', function($query){
        $query->where('user_id', Auth::user()->id);
    })->get();


    /* Get Investor */
    $investors= App\Models\Investor::with('vehicle')->get();

    $chartData = [
    'labels' => $investors->pluck('name'), // investor names
    'vehicles' => $investors->map(fn($item) => $item->vehicle->count()),
    'revenue' => $investors->map(function ($item) {
        $investorID = $item->id;
        $bookingIds = \App\Models\BookingData::whereHas('vehicle.investor', function ($q) use ($investorID) {
            $q->where('id', $investorID);
        })->pluck('booking_id')->unique();
        return \App\Models\BookingPaymentHistory::whereIn('booking_id', $bookingIds)->sum('paid_amount');
    }),
];










$vehicles2 = App\Models\Vehicle::with('bookingData')->get();

$bookingPriceMap   = [];
$bookingIds        = [];
$bookingVehicleMap = [];
$vehicleMap        = []; // vehicle_id => plate_no (or identifier)

// collect bookings and vehicle mapping
foreach ($vehicles2 as $vehicle) {
    $vehicleMap[$vehicle->id] = $vehicle->number_plate ?? $vehicle->id;

    foreach ($vehicle->bookingData as $booking2) {
        $bid = $booking2->booking_id;
        $bookingPriceMap[$bid]   = $booking2->price;
        $bookingIds[]            = $bid;
        $bookingVehicleMap[$bid] = $vehicle->id;
    }
}

// fetch payments summed per booking
$paymentsPerBooking = [];
if (!empty($bookingIds)) {
    $paymentsPerBooking = \App\Models\Payment::whereIn('booking_id', $bookingIds)
        ->selectRaw('booking_id, SUM(paid_amount) as paid_amount')
        ->groupBy('booking_id')
        ->pluck('paid_amount', 'booking_id')
        ->toArray();
}

// initialize per-vehicle paid/unpaid
$vehiclePaid   = [];
$vehicleUnpaid = [];

foreach ($vehicleMap as $vid => $plate) {
    $vehiclePaid[$vid]   = 0;
    $vehicleUnpaid[$vid] = 0;
}

// calculate paid/unpaid per vehicle
foreach ($bookingPriceMap as $bookingId => $price) {
    $paid      = $paymentsPerBooking[$bookingId] ?? 0;
    $vehicleId = $bookingVehicleMap[$bookingId];

    if ($paid >= $price) {
        $vehiclePaid[$vehicleId]   += $price;
    } elseif ($paid > 0 && $paid < $price) {
        $vehiclePaid[$vehicleId]   += $paid;
        $vehicleUnpaid[$vehicleId] += ($price - $paid);
    } else {
        $vehicleUnpaid[$vehicleId] += $price;
    }
}

// prepare chart data
$labels     = array_values($vehicleMap); // vehicle plate numbers
$paidData   = array_values($vehiclePaid);
$unpaidData = array_values($vehicleUnpaid);

$chartConfig1 = [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Paid',
                'data'  => $paidData,
                'backgroundColor' => 'green',
            ],
            [
                'label' => 'Not Paid',
                'data'  => $unpaidData,
                'backgroundColor' => 'red',
            ],
        ],
    ],
    'options' => [
        'responsive' => true,
        'plugins' => [
            'legend' => ['position' => 'top'],
            'title'  => [
                'display' => true,
                'text'    => 'SOA Report - Rental Amount per Car',
            ],
        ],
        'scales' => [
            'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Rental Amount']],
            'x' => ['title' => ['display' => true, 'text' => 'Car Plate No.']],
        ],
    ],
];

$soaChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig1));

//dd($paymentsPerBooking, $bookingPriceMap, $bookingVehicleMap);













$bookings = \App\Models\Booking::with(['bookingData', 'customer', 'payment', 'invoice', 'salePerson'])
    
    ->get();

$bookingsGrouped = $bookings->groupBy('customer_id')->map(function ($group) {
    $first = $group->first();

    $itemTotal = $group->reduce(function ($carry, $b) {
        if ($b->relationLoaded('bookingData') && $b->bookingData instanceof \Illuminate\Support\Collection) {
            return $carry + (float) $b->bookingData->sum('item_total');
        }
        return $carry + (float) ($b->item_total ?? 0);
    }, 0.0);

    $totalPrice = $group->reduce(function ($carry, $b) {
        if ($b->relationLoaded('bookingData') && $b->bookingData instanceof \Illuminate\Support\Collection) {
            return $carry + (float) ($b->bookingData->first()->price ?? 0);
        }
        return $carry;
    }, 0.0);

    $paidAmount = $group->reduce(function ($carry, $b) {
        if ($b->relationLoaded('payment')) {
            if ($b->payment instanceof \Illuminate\Support\Collection) {
                return $carry + (float) $b->payment->sum('paid_amount');
            }
            return $carry + (float) ($b->payment->paid_amount ?? 0);
        }
        return $carry;
    }, 0.0);

    $first->item_total     = $itemTotal;
    $first->total_price    = $totalPrice;
    $first->paid_amount    = $paidAmount;
    $first->bookings_count = $group->count();

    return $first;
})->values();

// prepare chart dataset
$customerNames   = $bookingsGrouped->pluck('customer.customer_name')->toArray();
$totalSales      = $bookingsGrouped->pluck('total_price')->toArray();
$totalPaid       = $bookingsGrouped->pluck('paid_amount')->toArray();

$chartConfig3 = [
    'type' => 'bar',
    'data' => [
        'labels' => $customerNames,
        'datasets' => [
            ['label' => 'Total Sale', 'data' => $totalSales],
            ['label' => 'Paid Amount', 'data' => $totalPaid],
        ],
    ],
    'options' => [
        'responsive' => true,
        'plugins' => ['legend' => ['position' => 'top']],
        'scales' => [
            'x' => ['ticks' => ['autoSkip' => false]], 
            'y' => ['beginAtZero' => true],
        ],
    ],
];

$customerChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig3));




    // group by customer
    $receivableData = $bookings->groupBy('customer_id')->map(function ($group) {
        $first = $group->first();

$totalInvoice = $group->reduce(function ($carry, $b) {
    if ($b->relationLoaded('invoice')) {
        if ($b->invoice instanceof \Illuminate\Support\Collection) {
            return $carry + (float) $b->invoice->sum('total_amount');
        }
        return $carry + (float) ($b->invoice->total_amount ?? 0);
    }
    return $carry;
}, 0.0);


        $totalPaid = $group->reduce(function ($carry, $b) {
            if ($b->relationLoaded('payment')) {
                if ($b->payment instanceof \Illuminate\Support\Collection) {
                    return $carry + (float) $b->payment->sum('paid_amount');
                }
                return $carry + (float) ($b->payment->paid_amount ?? 0);
            }
            return $carry;
        }, 0.0);

        $receivable = $totalInvoice - $totalPaid;

        return [
            'customer_name' => $first->customer->customer_name ?? 'Unknown',
            'total_invoice' => $totalInvoice,
            'total_paid'    => $totalPaid,
            'receivable'    => $receivable,
        ];
    })->values();

    // prepare chart arrays
    $customerNames = $receivableData->pluck('customer_name')->toArray();
    $receivables   = $receivableData->pluck('receivable')->toArray();

    // build chart
    $chartConfig4 = [
        'type' => 'bar',
        'data' => [
            'labels' => $customerNames,
            'datasets' => [
                [
                    'label' => 'Receivable Amount',
                    'data'  => $receivables,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                ],
            ],
        ],
        'options' => [
            'responsive' => true,
            'plugins' => ['legend' => ['position' => 'top']],
            'scales' => [
                'x' => ['ticks' => ['autoSkip' => false]],
                'y' => ['beginAtZero' => true],
            ],
        ],
    ];

    $receivableChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig4));


$salesmanStats = $bookings->groupBy('sale_person_id')->map(function ($group) {
    $first = $group->first();

    $totalInvoice = $group->reduce(function ($carry, $b) {
        if ($b->relationLoaded('invoice')) {
            if ($b->invoice instanceof \Illuminate\Support\Collection) {
                return $carry + (float) $b->invoice->sum('total_amount');
            }
            return $carry + (float) ($b->invoice->total_amount ?? 0);
        }
        return $carry;
    }, 0.0);

    $totalPaid = $group->reduce(function ($carry, $b) {
        if ($b->relationLoaded('payment')) {
            if ($b->payment instanceof \Illuminate\Support\Collection) {
                return $carry + (float) $b->payment->sum('paid_amount');
            }
            return $carry + (float) ($b->payment->paid_amount ?? 0);
        }
        return $carry;
    }, 0.0);

    $receivable = $totalInvoice - $totalPaid;

    return [
        'salesman_name' => $first->salePerson->name ?? 'Unknown',
        'total_invoice' => $totalInvoice,
        'total_paid'    => $totalPaid,
        'receivable'    => $receivable,
    ];
})->values();

$labels = $salesmanStats->pluck('salesman_name')->toArray();
$invoices = $salesmanStats->pluck('total_invoice')->toArray();
$paid = $salesmanStats->pluck('total_paid')->toArray();
$receivables = $salesmanStats->pluck('receivable')->toArray();

$chartConfig5 = [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => [
            ['label' => 'Total Invoice', 'data' => $invoices, 'backgroundColor' => 'rgba(54, 162, 235, 0.6)'],
            ['label' => 'Paid', 'data' => $paid, 'backgroundColor' => 'rgba(75, 192, 192, 0.6)'],
            ['label' => 'Receivable', 'data' => $receivables, 'backgroundColor' => 'rgba(255, 99, 132, 0.6)'],
        ],
    ],
];

$salesmanChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig5));

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
                            AED {{ number_format($totalAmount, 0) }}</h2>
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
                                <h4 class="card-title mb-3">Customer Sales</h4>
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
                              <h4 class="card-title mb-3">Customer Receivable</h4>
                              <div class="card-content">
                                 <img src="{{ $receivableChartUrl }}" alt="All Investors Chart" class="img-fluid">
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
