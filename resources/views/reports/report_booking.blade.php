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
                                <table class="table table-striped table-hover" style="width:100%;">
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
                                        @php $totalPrice = 0; @endphp
                                        @foreach ($booking as $item)
                                        @php $totalPrice += $item->price; @endphp
                                        <tr>
                                            <td>{{ $item->booking->agreement_no }}</td>
                                            <td>{{ $item->booking->customer->customer_name }}</td>
                                            <td>{{ $item->invoice->zoho_invoice_number }}</td>
                                            <td>
                                                @php
                                                    $vehicle_name= $item->vehicle->vehicle_name ?? $item->vehicle->temp_vehicle_detail;
                                                    $no_plate= $item->vehicle->number_plate;
                                                @endphp
                                                {{ $vehicle_name }} | {{ $no_plate }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</td>
                                            <td>{{ $item->price }}</td>
                                            <td>
                                                @switch($item->transaction_type)
                                                    @case(1) Rent @break
                                                    @case(2) Renew @break
                                                    @case(3) Fine @break
                                                    @case(4) Salik @break
                                                    @default Unknown
                                                @endswitch
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-right font-weight-bold">Total</td>
                                            <td class="font-weight-bold">{{ number_format($totalPrice, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
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
