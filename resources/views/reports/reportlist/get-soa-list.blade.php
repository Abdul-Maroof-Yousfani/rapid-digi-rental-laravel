@php $totalAmount = 0; @endphp
@foreach ($bookingData as $item)
    @php
        $amount = $item->price; // Assuming there's a `rental_amount` column
        $totalAmount += $amount;
    @endphp
    <tr>
        <td>{{ $item->vehicle->number_plate }}</td>
        <td>
            {{ $item->vehicle->temp_vehicle_detail ??
            ($item->vehicle->vehicle_name.' '.$item->vehicle->car_make .' '.$item->vehicle->year) }}</td>
        <td>Rented</td>
        <td class="rental-amount">{{ number_format($amount, 2) }}</td>
    </tr>
@endforeach


<script>
    // document.getElementById('totalAmount').innerText = "{{ number_format($totalAmount, 2) }}";
    // document.getElementById('netAmount').innerText = "{{ number_format($totalAmount * 0.8, 2) }}";
    // document.getElementById('printTotalAmount').innerText = "{{ number_format($totalAmount, 2) }}";
    // document.getElementById('printNetAmount').innerText = "{{ number_format($totalAmount * 0.8, 2) }}";
    $("#totalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#netAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
    $("#printTotalAmount").text('{{ number_format($totalAmount, 2) }}');
    $("#printNetAmount").text('{{ number_format($totalAmount * 0.8, 2) }}');
</script>
