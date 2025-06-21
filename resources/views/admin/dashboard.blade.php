@extends('admin.master-main')
@section('content')
@php
    $booking= App\Models\Booking::count();
    $customers= App\Models\Customer::count();
    $revenue= App\Models\Payment::sum('paid_amount');
    $receiveable= App\Models\Payment::sum('pending_amount');

    $totalAmount= App\Models\BookingData::with('vehicle', 'invoice')
            ->whereHas('invoice', function($q){
                $q->where('invoice_status', 'sent');
            })
            ->whereHas('vehicle.investor', function($q){
                $q->where('user_id', Auth::user()->id);
            })
            ->whereIn('transaction_type', ['1', '2'])->sum('price');

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
                    <h2 class="mb-3 font-18">
                        @if (Auth::user()->getRoleNames()->first() == 'investor')
                            AED {{ number_format($totalAmount, 0) }}</h2>
                        @else
                            AED {{ number_format($revenue, 0) }}</h2>
                        @endif
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
    <div class="row" style="display: none;">
        <div class="col-md-12">
            @php
                $lineItems= App\Models\BookingData::with('vehicle', 'invoice')
                            ->whereHas('invoice', function($q){
                                $q->where('invoice_status', 'sent');
                            })
                            ->whereHas('vehicle.investor', function($q){
                                $q->where('user_id', Auth::user()->id);
                            })
                            ->whereIn('transaction_type', ['1', '2'])->get();
            @endphp
            <div class="card">
                <div class="card-header">
                    {{ $totalAmount }}
                </div>
                <div class="card-body">
                    <table class="table table-stripped table-hover" id="tableExport">
                        <thead>
                            <tr>
                                <td>Invoice</td>
                                <td>Vehicle</td>
                                <td>price</td>
                                <td>Investor</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lineItems as $item)
                            <tr>
                                <td>{{ $item->invoice->zoho_invoice_number }}</td>
                                <td>{{ $item->vehicle->vehicle_name ?? $item->vehicle->temp_vehicle_detail }}</td>
                                <td>{{ $item->price }}</td>
                                <td>{{ $item->vehicle->investor->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




  </section>
</div>


@endsection
