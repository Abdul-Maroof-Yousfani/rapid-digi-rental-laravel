@extends('admin.master-main')
@section('content')

  <div class="main-content">
        <section class="section">
               <div class="section-body">
<form method="GET" action="{{ route('investor.bookingReport') }}" class="mb-4">
    <div class="form-row align-items-end">
        <div class="col-md-3">
            <label for="from_date">From</label>
            <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label for="to_date">To</label>
            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary mt-4 w-100">Filter</button>
        </div>
    </div>
</form>



<div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="" style="width:100%;">
                        <thead>
                           <tr>
            <th>Agreement No</th>
            <th>Customer</th>
              <th>Invoice No</th>
            <th>Vehicle</th>
            <th>Booking Dates</th>
            <th>Price</th>
            <th>Transaction Type</th>
          
        </tr>
                        </thead>
                      <tbody>
@foreach($bookings as $booking)
    @php $first = true; @endphp
    @foreach($booking->bookingData as $data)
        <tr>
            @if($first)
                <td rowspan="{{ count($booking->bookingData) }}">{{ $booking->agreement_no }}</td>
                <td rowspan="{{ count($booking->bookingData) }}">{{ $booking->customer->customer_name ?? 'N/A' }}</td>
            @endif
              @if($first)
                <td rowspan="{{ count($booking->bookingData) }}">
                    {{ $booking->invoice->first()->zoho_invoice_number ?? 'N/A' }}
                </td>
                @php $first = false; @endphp
            @endif
            <td>{{ $data->vehicle->vehicle_name ?? $data->vehicle->temp_vehicle_detail }}</td>
           

           <td>
    {{ \Carbon\Carbon::parse($data->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($data->end_date)->format('d M Y') }}
</td>

            <td>{{ $data->price }}</td>

           <td>
    @switch($data->transaction_type)
        @case(1)
            Rent
            @break
        @case(2)
            Renew
            @break
        @case(3)
            Fine
            @break
        @case(4)
            Salik
            @break
        @default
            Unknown
    @endswitch
</td>

          
        </tr>
    @endforeach
@endforeach
</tbody>

                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>


</div>
</section>

</div>
@endsection
