@php $number=1; @endphp
@foreach ($customers as $item)
<tr>
    <td>{{ $number }}.</td>
    <td>{{ $item->customer_name }}</td>
    <td>{{ $item->email }}</td>
    <td>{{ $item->phone }}</td>
    <td>{{ $item->cnic }}</td>
    <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
    <td>
        <a href='@can('manage customers') {{ role_base_url("customer/".$item->id."/edit") }} @endcan' class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
        <form action="{{ auth()->user()->hasRole('admin') ? url('admin/customer/'.$item->id) : url('booker/customer/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>
                Delete
            </button>
        </form>

    </td>
</tr>
@php $number++; @endphp
@endforeach
