@php $number = 1; @endphp
@foreach ($payment as $item)
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->booking && $item->booking->customer ? $item->booking->customer->customer_name : 'No Customer'}}</td>
        <td>{{ $item->booking ? $item->booking->id : '-' }}</td>
        <td>{{ $item->booking && $item->booking->invoice ? ($item->booking->invoice->zoho_invoice_number ?? '-') : '-' }}
        </td>
        <td>{{ $item->paymentMethod ? $item->paymentMethod->name : '-' }}</td>
        <td>{{ number_format($item->booking_amount ?? 0, 2) }}</td>
        <td>{{ number_format($item->paid_amount ?? 0, 2) }}</td>
        <td>{{ number_format($item->pending_amount ?? 0, 2) }}</td>
        <td>
            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown"
                    aria-expanded="false">
                    Actions
                </button>
                <div class="dropdown-menu">
                    <button type="button" class="dropdown-item paymentHistory" data-payment-id="{{ $item->id }}"
                        data-toggle="modal" data-target="#paymentHistoryModal">
                        <i class="fas fa-eye"></i> View
                    </button>
                    @if(!empty($item->receipt))
                        <a href="{{ asset($item->receipt) }}" target="_blank" class="dropdown-item">
                            <i class="fas fa-paperclip"></i> View Attachment
                        </a>
                    @endif
                    @can('delete payment')
                        <form action="{{ route('payment.destroy', $item->id) }}" method="POST" style="display:inline;"
                            class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item delete-confirm text-danger">
                                <i class="far fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </td>
    </tr>
    @php $number++; @endphp
@endforeach

<tr>
    <td colspan="9" class="text-center">
        <div class="d-flex justify-content-center">
            {{ $payment->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>