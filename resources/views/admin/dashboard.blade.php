@php
    $userRole = Auth::user()->getRoleNames()->first();

    if ($userRole == 'investor') {
        $investorId = Auth::id();

        $revenue = \App\Models\BookingData::whereHas('vehicle', function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            })
            ->whereHas('invoice', function ($q) {
                $q->where('invoice_status', 'sent');
            })
            ->whereHas('invoice.paymentData', function ($q) {
                $q->where('status', 'paid');
            })
            ->with(['invoice.paymentData' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->get()
            ->sum(function ($bookingData) {
                return $bookingData->invoice->paymentData->sum('paid_amount');
            });

    } else {
        // Admin ya Booker
        $revenue = \App\Models\Payment::sum('paid_amount');
    }

    $booking = \App\Models\Booking::count();
    $customers = \App\Models\Customer::count();
    $receiveable = \App\Models\Payment::sum('pending_amount');
@endphp

@extends('admin.master-main')
@section('content')
{{-- @php
    $booking= App\Models\Booking::count();
    $customers= App\Models\Customer::count();
    $revenue= App\Models\Payment::sum('paid_amount');
    $receiveable= App\Models\Payment::sum('pending_amount');
@endphp --}}
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
                    {{-- <p class="mb-0"><span class="col-green">10%</span> Increase</p> --}}
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
                    {{-- <p class="mb-0"><span class="col-orange">09%</span> Decrease</p> --}}
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
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row ">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Receivable</h5>
                    <h2 class="mb-3 font-18">AED {{ number_format($receiveable, 0) }}</h2>
                    {{-- <p class="mb-0"><span class="col-green">18%</span> Increase</p> --}}
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
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row ">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Revenue</h5>
                    <h2 class="mb-3 font-18">AED {{ number_format($revenue, 0) }}</h2>
                    {{-- <p class="mb-0"><span class="col-green">42%</span> Increase</p> --}}
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




  </section>
</div>


@endsection
