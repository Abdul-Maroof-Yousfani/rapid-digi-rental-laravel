@php
  $number = 1;
  $totalPaymentReceive = 0;
  $totalOutstanding = 0;
@endphp

@foreach ($ledgerData as $item)
  @php
    $totalPaymentReceive += $item->payment_receive;
    $totalOutstanding += $item->outstanding;
  @endphp

  <tr>
    <td>{{ $item->date }}</td>
    <td>{{ $item->invoice_number }}</td>
    <td>{{ $item->description }}</td>
    <td>{{ $item->item_desc }}</td>
    <td align="right">{{ $item->invoice_amount }}</td>
    <td align="right">{{ number_format($item->payment_receive, 2) }}</td>
    <td align="right">{{ number_format($item->outstanding, 2) }}</td>
    <td>{{ $item->invoice_status }}</td>
  </tr>
  @php $number++; @endphp
@endforeach

@if(count($ledgerData) > 0)
<tr>
  <td colspan="5" align="right"><b>Sub Total</b></td>
  <td align="right"><b>{{ number_format($totalPaymentReceive, 2) }}</b></td>
  <td align="right"><b>{{ number_format($totalOutstanding, 2) }}</b></td>
  <td></td>
</tr>
@else
<tr>
  <td colspan="8" class="text-center">No records found</td>
</tr>
@endif
