@php $number = 1; @endphp
@foreach ($booking as $item)
    @php $childCount = $item->bookingData->count(); @endphp

    {{-- Parent Row --}}
    <tr>
        {{-- S. No with rowspan --}}
        <td rowspan="{{ $childCount + 1 }}" class="align-middle">{{ $number }}.</td>
        <td>{{ $item->customer->customer_name }}</td>
        <td>{{ $item->id }}</td>
        <td>{{ $item->total_price }}</td>
        <td>{{ $item->payment->paid_amount ?? 0 }}</td>
        <td>{{ $item->payment->pending_amount ?? 0 }}</td>
    </tr>

    {{-- Child Rows --}}
    @foreach ($item->bookingData as $bd)
        <tr>
            {{-- Empty <td> removed because rowspan is handling S. No --}}
            <td class="px-5" colspan="2">Vehicle: {{ $bd->vehicle->vehicle_name ?? $bd->vehicle->temp_vehicle_detail }}</td>
            <td>Price: {{ $bd->price }}</td>
            <td>Qty: {{ $bd->quantity }}</td>
            <td>
                @switch($bd->transaction_type)
                    @case(1) Rent @break
                    @case(2) Renew @break
                    @case(3) Fine @break
                    @case(4) Salik @break
                @endswitch
            </td>
        </tr>
    @endforeach

    @php $number++; @endphp
@endforeach
