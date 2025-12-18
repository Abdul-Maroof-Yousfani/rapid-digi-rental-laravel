@php
  $number = 1;
  $totalInvoiceAmount = 0;
  $totalPaymentReceive = 0;
  $totalOutstanding = 0;
@endphp

@foreach ($ledgerData as $item)
  @php
    $totalInvoiceAmount += $item->invoice_amount;
    $totalPaymentReceive += $item->payment_receive;
    $totalOutstanding += $item->outstanding;
  @endphp

  <tr>
    <td>{{ $item->date }}</td>
    <td>
      @if($item->invoice_id && $item->invoice_number)
        <a href="{{ route('view.invoice', $item->invoice_id) }}" target="_blank" style="color: #0d6efd; text-decoration: underline;">
          {{ $item->invoice_number }}
        </a>
      @else
        {{ $item->invoice_number }}
      @endif
    </td>
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
  <td colspan="4" align="right"><b>Sub Total</b></td>
  <td align="right"><b>{{ number_format($totalInvoiceAmount, 2) }}</b></td>
  <td align="right"><b>{{ number_format($totalPaymentReceive, 2) }}</b></td>
  <td align="right"><b>{{ number_format($totalOutstanding, 2) }}</b></td>
  <td></td>
</tr>
@else
<tr>
  <td colspan="8" class="text-center">No records found</td>
</tr>
@endif
