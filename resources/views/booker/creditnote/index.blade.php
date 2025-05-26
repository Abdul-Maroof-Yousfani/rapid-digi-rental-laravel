@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')

      <!-- Main Content -->
        <div class="main-content">
            <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Credit Note List</h3>
                            <a href="{{ role_base_route('credit-note.create') }}" class="btn btn-primary">
                                Create CN
                            </a>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Credit No.</th>
                                        <th>Customer</th>
                                        <th>Agreement no</th>
                                        <th>Refund Method</th>
                                        <th>Initial Deposit</th>
                                        <th>Remaining</th>
                                        <th>Refund</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($creditNote as $item)
                                    <tr>
                                        <td>{{ $item->credit_note_no }}</td>
                                        <td>{{ $item->booking->customer->customer_name }}</td>
                                        <td>{{ $item->booking->agreement_no }}</td>
                                        <td>{{ $item->paymentMethod->name }}</td>
                                        <td>{{ $item->booking->deposit->deposit_amount }}</td>
                                        <td>{{ $item->remaining_deposit }}</td>
                                        <td>{{ $item->refund_amount }}</td>
                                        <td>
                                            <a href="" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                            <form action="" method="" style="display:inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.delete-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
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
