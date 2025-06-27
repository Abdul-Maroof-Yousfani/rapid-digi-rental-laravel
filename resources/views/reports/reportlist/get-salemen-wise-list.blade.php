@php
    $number = 1;
    $bookingtotal = 0;
@endphp
@foreach ($booking as $item)
    @php
        $itemBookingTotal = $item->invoice ? $item->invoice->sum('total_amount') : 0;
        $bookingtotal += $itemBookingTotal;
    @endphp
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->agreement_no }}</td>
        <td>{{ $item->customer->customer_name }}</td>
        <td align="right">{{ number_format($itemBookingTotal, 2) }}</td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="3" align="right"><b>Sub Total</b></td>
    <td align="right"><b>{{ number_format($bookingtotal, 2) }}</b></td>
</tr>
