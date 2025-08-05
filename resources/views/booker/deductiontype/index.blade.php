@extends('admin.master-main')
@php $userRole= Auth::user()->getRoleNames()->first(); @endphp
@section('title', ucfirst($userRole." "."Portal"))
@section('content')

<style>
    .spinner-border.custom-blue {
        width: 3rem;
        height: 3rem;
        border-width: 0.4rem;
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: #0d6efd;
        border-left-color: rgba(13, 110, 253, 0.25);
    }
</style>


<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Invoice Type List</h3>
                            <a href="{{ route('invoice-type.create') }}" class="btn btn-primary">
                                Create IT
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="filterForm">
                                <div class="row">
                                    <div class="col-3 ml-auto">
                                        <input type="text" placeholder="Search" class="form-control" id="search">
                                    </div>
                                </div><br>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>S.no</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    @php $index = 1; @endphp
                                    <tbody id="deductionTypeList">
                                        @foreach ($deductionType as $item)

                                        <tr>
                                            <td>{{ $index++ }}</td>
                                            <td>{{ $item->name ?? 'N/A' }}</td>
                                            <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>

                                            <td>
                                                <a href='{{ url("invoice-type/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                                                <form action="{{ url('invoice-type/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $deductionType->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection


@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#search').on('keyup', function() {
            let search = $(this).val();
            $('#deductionTypeList').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            $.ajax({
                url: '/search-deductiontype',
                method: 'get',
                data: {
                    search: search
                },
                success: function(response) {
                    let html = '';
                    if (response.deductionType.length > 0) {
                        $.each(response.deductionType, function(index, data) {
                            html += `
                                    <tr data-id="${data.id}">
                                        <td>${data.credit_note_no}</td>
                                        <td>${data.booking.customer.customer_name}</td>
                                        <td>${data.booking.agreement_no}</td>
                                        <td>${data.booking?.paymentMethod?.name ?? 'N/A'}</td>
                                        <td>${data.booking.deposit.deposit_amount}</td>
                                        <td>${data.remaining_deposit}</td>
                                        <td>${data.refund_amount}</td>
                                        <td>
                                            <a href="view-credit-note/${data.id}" class="btn btn-sm btn-primary"> <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                `;
                        });
                    } else {
                        html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                    }


                    $('#deductionTypeList').html(html);
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-confirm');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Stop form submit
                const form = this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>

@endsection