@php
    $number = 1;
    $bookingtotal = 0;
    $paidtotal = 0;
    $receivabletotal = 0;
@endphp
@foreach ($booking as $item)

    @php
        // Calculate totals per item
        $itemBookingTotal = $item->invoice ? $item->invoice->sum('total_amount') : 0;
        $itemPaidTotal = $item->payment ? $item->payment->paid_amount : 0;
        $itemReceivableTotal = $item->payment ? $item->payment->pending_amount : $itemBookingTotal;

        // Accumulate totals
        $bookingtotal += $itemBookingTotal;
        $paidtotal += $itemPaidTotal;
        $receivabletotal += $itemReceivableTotal;
    @endphp

    <tr>
        <td>{{ $number }}.</td>
        <td>
            {{ $item->agreement_no }}
        </td>
        <td align="right">{{ number_format($itemBookingTotal, 2) }}</td>
        <td align="right">{{ number_format($itemPaidTotal, 2) }}</td>
        <td align="right">{{ number_format($itemReceivableTotal, 2) }}</td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="2" align="right"><b>Sub Total</b></td>
    <td align="right"><b>{{ $bookingtotal }}</b></td>
    <td align="right"><b>{{ $paidtotal }}</b></td>
    <td align="right"><b>{{ $receivabletotal }}</b></td>
</tr>
