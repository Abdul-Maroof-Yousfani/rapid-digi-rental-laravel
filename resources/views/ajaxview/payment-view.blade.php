@php $number = 1; @endphp
@foreach ($payment as $item)
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->booking->customer->customer_name ?? 'No Customer'}}</td>
        <td>{{ $item->booking->id }}</td>
        <td>{{ $item->booking->invoice->zoho_invoice_number ?? '-' }}</td>
        <td>{{ $item->paymentMethod->name }}</td>
        <td>{{ number_format($item->booking_amount, 2) }}</td>
        <td>{{ number_format($item->paid_amount, 2) }}</td>
        <td>{{ number_format($item->pending_amount, 2) ?? 0 }}</td>
        <td>
            <button type="button" class="btn btn-success btn-sm paymentHistory"
                data-payment-id="{{ $item->id }}" data-toggle="modal" data-target="#paymentHistoryModal">
                View
            </button>
        </td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="8" class="text-center">
        <div class="d-flex justify-content-center">
            {{ $payment->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>
