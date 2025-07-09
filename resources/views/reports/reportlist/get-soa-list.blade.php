@php $totalAmount = 0; @endphp
@foreach ($vehicles as $item)
    @php
        // Already passed from controller
        $bookingsInRange = $item->bookingData->filter(function ($booking) use ($from, $to) {
            return $booking->start_date <= $to && $booking->end_date >= $from;
        });

        $price = $bookingsInRange->sum('price');
        $isRented = $bookingsInRange->isNotEmpty();

        $totalAmount += $price;
    @endphp

    <tr>
        <td>{{ $item->number_plate }}</td>
        <td>
            {{ $item->temp_vehicle_detail ?? ($item->vehicle_name . ' ' . $item->car_make . ' ' . $item->year) }}
        </td>
        <td>{{ $isRented ? 'Monthly' : 'Not Rented' }}</td>
        <td class="rental-amount">{{ number_format($price, 2) }}</td>
    </tr>
@endforeach



<script>
    $("#totalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#netAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
    $("#printTotalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#printNetAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
</script>
