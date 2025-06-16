@php $number= 1; @endphp

@foreach ($booking as $item)
    {{-- Parent Row --}}
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->customer->customer_name }}</td>
        <td>{{ $item->id }}</td>
        <td>{{ $item->total_price }}</td>
        <td>{{ $item->payment->paid_amount ?? 0 }}</td>
        <td>{{ $item->payment->pending_amount ?? 0 }}</td>
    </tr>

    {{-- Child Rows for each bookingData --}}
    @foreach ($item->bookingData as $bd)
        <tr>
            <td></td>
            <td class="px-5" colspan="2">Vehicle: {{ $bd->vehicle->vehicle_name ?? $bd->vehicle->temp_vehicle_detail }}</td>
            <td>Price: {{ $bd->price }}</td>
            <td>Qty: {{ $bd->quantity }}</td>
            <td>
                @if ($bd->transaction_type == 1)
                    {{ 'Rent' }}
                @elseif($bd->transaction_type == 2)
                    {{ 'Renew' }}
                @elseif($bd->transaction_type == 3)
                    {{ 'Fine' }}
                @elseif($bd->transaction_type == 4)
                    {{ 'Salik' }}
                @endif
            </td>
        </tr>
    @endforeach
    @php $number++ @endphp
@endforeach
