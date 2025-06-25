@php $number=1; @endphp
@foreach ($payment as $item)
    <tr>
        <td>{{ $number }}.</td>
        <td>{{ $item->booking->customer->customer_name }}</td>
        <td>{{ $item->booking->agreement_no }}</td>
        <td>{{ $item->paymentMethod->name }}</td>
        <td>{{ $item->booking_amount }}</td>
        <td>{{ $item->paid_amount }}</td>
        <td>{{ $item->pending_amount ?? 0 }}</td>
        <td>
            {{-- <a href="{{ url(Auth::user()->getRoleNames()->first().'/payment-history/'.$item->id) }}" class="btn btn-primary btn-sm"><i class="far fa-eye"></i> View </a> --}}
            <button type="button" class="btn btn-success btn-sm paymentHistory" data-invoice-id="{{ $item->id }}" data-toggle="modal" data-target="#paymentHistoryModal">
                Detail
            </button>
        </td>
    </tr>
@php $number++; @endphp
@endforeach


