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
            <a href="" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
            <form action="" method="" style="display:inline;" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
            </form>
        </td>
    </tr>
@php $number++; @endphp
@endforeach


