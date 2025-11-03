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
    <td>{{ $number }}.</td>
    <td>{{ $item->agreement_no }}</td>
    <td>
      {{ $item->bookingData->pluck('invoice.zoho_invoice_number')->first() }}
    </td>

    <td>{{ $item->customer->customer_name }}</td>
    <td>{{ $item->salePerson->name ?? '-'}}</td>
     <td align="right">{{ number_format($itemPaidTotal, 2) }}</td>
    {{-- <td align="right">{{ number_format($receivableAmt, 2) }}</td> --}}
  </tr>
  @php $number++; @endphp
@endforeach

<tr>
  <td colspan="5" align="right"><b>Sub Total</b></td>
  <td align="right"><b>{{ number_format($paidtotal, 2) }}</b></td>
</tr>