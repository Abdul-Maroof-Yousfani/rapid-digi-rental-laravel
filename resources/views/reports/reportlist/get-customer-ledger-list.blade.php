@php
  $number = 1;
  $totalInvoiceAmount = 0;
  $totalPaymentReceive = 0;
  $totalOutstanding = 0;
  $countedInvoiceIds = []; // track which invoices are already counted
    $totalInvoices = count($ledgerData);

@endphp

@foreach ($ledgerData as $item)
  @php
    // Only add invoice_amount once per invoice_id
    if ($item->invoice_id && !in_array($item->invoice_id, $countedInvoiceIds, true)) {
        $totalInvoiceAmount += $item->invoice_amount;
        $countedInvoiceIds[] = $item->invoice_id;
    }

    $totalPaymentReceive += $item->payment_receive;
    $totalOutstanding   += $item->outstanding;
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
@endforeach

@if(count($ledgerData) > 0)
@php
  $uniqueInvoiceCount = count($countedInvoiceIds);
@endphp
<tr class="ledger-totals-row">
  <td colspan="3" align="right"><b>Total Invoice</b></td>
  <td align="center"><b>{{ $uniqueInvoiceCount }}</b></td>
  <td align="right"><b>{{ number_format($totalInvoiceAmount, 2) }}</b></td>
  <td align="right"><b>{{ number_format($totalPaymentReceive, 2) }}</b></td>
  <td align="right"><b>{{ number_format($totalInvoiceAmount-$totalPaymentReceive, 2) }}</b></td>
  <td></td>
</tr>
@else
<tr>
  <td colspan="8" class="text-center">No records found</td>
</tr>
@endif
