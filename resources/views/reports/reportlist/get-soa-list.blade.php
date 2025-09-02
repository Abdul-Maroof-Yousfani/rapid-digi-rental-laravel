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
        @if ($item->bookingData->isNotEmpty())
            @foreach ($item->bookingData as $booking)
                <td>{{ $booking->invoice->zoho_invoice_number ?? '-' }}</td>
            @endforeach
        @else
            <td>-</td>
        @endif
        <td>
            {{ $item->temp_vehicle_detail ?? ($item->vehicle_name . ' ' . $item->car_make . ' ' . $item->year) }}
        </td>
        <td>{{ $isRented ? 'Monthly' : 'Not Rented' }}</td>
        <td class="rental-amount">{{ number_format($price, 2) }}</td>
        <td>
            @php
                $bookingPayment = $payments->firstWhere('booking_id', $bookingsInRange->first()->booking_id ?? null);
                $paidAmount = $bookingPayment->paid_amount ?? 0;
                $bookingAmount = $bookingPayment->booking_amount ?? 0;

                if ($paidAmount == 0 && $isRented == 'Monthly') {
                    $status = 'Pending';
                } elseif ($paidAmount == 0) {
                    $status = '-';
                } elseif ($paidAmount >= $price) {
                    $status = 'Paid';
                } else {
                    $status = 'Partially Paid';
                }
            @endphp

            {{ $status }}
        </td>

    </tr>
@endforeach

<script>
    $("#totalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#netAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
    $("#printTotalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#printNetAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
</script>