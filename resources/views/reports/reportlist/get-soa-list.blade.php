@php $totalAmount = 0; @endphp
@foreach ($vehicles as $item)
    @php
        $from = \Carbon\Carbon::parse($month)->startOfMonth();
        $to = \Carbon\Carbon::parse($month)->endOfMonth();

        // Filter related bookings for the selected month
        $bookingsInMonth = $item->bookingData->filter(function ($booking) use ($from, $to) {
            return $booking->start_date <= $to && $booking->end_date >= $from;
        });

        // Assume one booking per vehicle per month for simplicity (if more, you can sum them)
        $price = $bookingsInMonth->sum('price');
        $isRented = $bookingsInMonth->isNotEmpty();

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
