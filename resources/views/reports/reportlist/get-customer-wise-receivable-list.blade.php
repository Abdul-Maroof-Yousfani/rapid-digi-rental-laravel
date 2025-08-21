@php
    $number = 1;
    $bookingtotal = 0;
    $paidtotal = 0;
    $receivabletotal = 0;
@endphp
@foreach ($booking as $item)
@php
      $price = $item->bookingData()->first()?->price ?? 0;
      $paidAmount = $item->payment->paid_amount ?? 0;
      if($price <= $paidAmount){
        $pending_amt = $price;
        $rece_amt = 0;
      }
      else if($price > $paidAmount){
        $pending_amt = $paidAmount;
        $rece_amt = $price - $paidAmount;
      }
      

        // Calculate totals per item
        $itemBookingTotal = $item->invoice ? $item->invoice->sum('total_amount') : 0;
        $itemPaidTotal = $item->payment ? $paidAmount : 0;
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
        <td>{{ $item->customer->customer_name }}</td>
        <td align="right">{{ number_format($itemBookingTotal, 2) }}</td>
        <td align="right">{{ number_format($itemPaidTotal, 2) }}</td>
        <td align="right">{{ number_format($itemReceivableTotal, 2) }}</td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="3" align="right"><b>Sub Total</b></td>
    <td align="right"><b>{{ number_format($bookingtotal, 2) }}</b></td>
    <td align="right"><b>{{ number_format($paidtotal, 2) }}</b></td>
    <td align="right"><b>{{ number_format($receivabletotal, 2) }}</b></td>
</tr>
