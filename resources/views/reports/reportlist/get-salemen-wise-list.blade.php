@php
    $number = 1;
    $paidtotal = 0;
    $bookingtotal = 0;
@endphp
@foreach ($booking as $item)
    @php
        $itemBookingTotal = $item->invoice ? $item->invoice->sum('total_amount') : 0;
        $price = $item->bookingData()->first()?->price ?? 0;
     $paidAmount = $item->payment?->paid_amount ?? 0;
      if($price <= $paidAmount){
        $paid_amt = $price;
        $rece_amt = 0;
      }
      else if($price > $paidAmount){
        $paid_amt = $paidAmount;
        $rece_amt = $price - $paidAmount;
      }

        $bookingtotal += $itemBookingTotal;
        $paidtotal += $paid_amt;

    @endphp
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->agreement_no }}</td>
        <td>{{ $item->customer->customer_name }}</td>
        <!-- <td align="right">{{ number_format($itemBookingTotal, 2) }}</td> -->
        <td align="right">{{ number_format($paid_amt, 2) }}</td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="3" align="right"><b>Sub Total</b></td>
    <td align="right"><b>{{ number_format($paidtotal, 2) }}</b></td>
</tr>
