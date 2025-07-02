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
    $vehicles= App\Models\Vehicle::with('investor')
        ->whereHas('investor', function($query){
            $query->where('user_id', Auth::user()->id);
        })->count();

    /* Get Investor */
    $investors= App\Models\Investor::with('vehicle')->get();

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
                                <img src="{{ asset('assets/img/banner/3.png') }}" alt="">
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

    @if (!Auth::user()->hasRole('investor'))
        <div class="row">
            @foreach ($investors as $item)
            @php
                $investorID= $item->id;
                $bookingIds = App\Models\BookingData::whereHas('vehicle.investor', function ($q) use ($investorID) {
                    $q->where('id', $investorID);
                })->pluck('booking_id')->unique();
                $totalAmount = App\Models\BookingPaymentHistory::whereIn('booking_id', $bookingIds)->sum('paid_amount');
            @endphp
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                        <div class="row ">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                            <div class="card-content">
                                <h3>{{ $item->name }}</h3>
                                <p class="mb-3 font-18">
                                    Total vehicles: {{ $item->vehicle->count() }} <br>
                                    Revenue: {{ number_format($totalAmount, 0) }}
                                </p>
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
            @endforeach
        </div>
    @endif




  </section>
</div>


@endsection
