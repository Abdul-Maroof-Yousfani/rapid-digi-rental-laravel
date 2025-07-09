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
    <td>{{ number_format($item->price, 2) }}</td>
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


<tr>
    <td colspan="5" class="text-right font-weight-bold">Total</td>
    <td class="font-weight-bold">{{ number_format($totalPrice, 2) }}</td>
    <td></td>
</tr>
