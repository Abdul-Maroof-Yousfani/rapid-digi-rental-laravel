@php
  $number = 1;
  $bookingtotal = 0;
  $paidtotal = 0;
  $receivabletotal = 0;
@endphp

@foreach ($booking as $item)
  @php
    $price = $item->bookingData->sum('item_total');
    $paidAmount = $item->payment->paid_amount ?? 0;

    // Determine pending & receivable amounts
     $receivableAmt = max($price - $paidAmount, 0);
    $itemBookingTotal = $item->invoice ? $item->invoice->sum('total_amount') : 0;
    $itemPaidTotal = $paidAmount;
    //$itemReceivableTotal = $item->payment->pending_amount ?? $receivableAmt;

    // Accumulate totals
    $bookingtotal += $price;
    $paidtotal += $itemPaidTotal;
    $receivabletotal += $receivableAmt;
  @endphp

  <tr>
    <td>{{ $item->invoice->invoice_date }}</td>
    <td>
      {{ $item->bookingData->pluck('invoice.zoho_invoice_number')->filter()->unique()->implode(', ') }}
    </td>
    <td>{{ $item->bookingData->first()->description }}</td>
    <td> Item Desc </td>
    <td align="right">{{ number_format($price, 2) }}</td>
    <td align="right">{{ number_format($itemPaidTotal, 2) }}</td>
    <td align="right">{{ number_format($receivableAmt, 2) }}</td>
    <td>{{ $item->invoice->invoice_status }}</td>

  </tr>
@endforeach

<tr>
  <td colspan="4" align="right"><b>Sub Total</b></td>
  <td align="right"><b>{{ number_format($bookingtotal, 2) }}</b></td>
  <td align="right"><b>{{ number_format($paidtotal, 2) }}</b></td>
  <td align="right"><b>{{ number_format($receivabletotal, 2) }}</b></td>
</tr>
