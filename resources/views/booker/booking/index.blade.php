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
                      <h3 class="mb-0">Booking List</h3>
                      <a href="{{ role_base_route('customer-booking.create') }}" class="btn btn-primary">
                        Add Booking
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
                        <table class="table table-striped table-hover" id="" style="width:100%;">
                        <thead>
                            <tr>
                            <th>S.no</th>
                            <th>Customer</th>
                            <th>Agreement No.</th>
                            <th>Sale Person</th>
                            <th>Deposit</th>
                            <th>Booking</th>
                            <th>Payment</th>
                            <th>Total Price</th>
                            <th align="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $now = \Carbon\Carbon::now();
                                $number=1;
                            @endphp
                            @foreach ($booking as $item)
                            <tr>
                                @php $firstInvoice = $item->invoice->first(); @endphp
                                <td>{{ $item->id }}.</td>
                                <td>{{ $item->customer->customer_name ?? 0 }}</td>
                                <td>{{ $item->agreement_no ?? 0 }}</td>
                                <td>{{ $item->salePerson->name ?? 'N/A' }}</td>
                                <td>{{ $item->deposit->deposit_amount ?? 0 }}</td>
                                <td>{{ $item->booking_status ?? 'overdue' }}</td>
                                <td>{{ $item->payment->payment_status ?? "pending" }}</td>
                                <td>{{ $firstInvoice->total_amount }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">

                                            <button class="dropdown-item close-booking" data-booking-id="{{ $item->id }}" {{ $item->booking_status=='closed' ? 'disabled' : '' }}>
                                                <i class="fas fa-lock"></i> Close Booking
                                            </button>

                                            <a class="dropdown-item" href="{{ url('booker/booking/'. $item->id) }}"> <i class="fas fa-eye"></i> View </a>
                                            <a class="dropdown-item" href="{{ url('booker/customer-booking/'.$item->id.'/edit') }}"> <i class="far fa-edit"></i> Edit </a>
                                            @if (is_null($item->started_at) || \Carbon\Carbon::parse($item->started_at)->isAfter($now))
                                            <a class="dropdown-item" href=""> <i class="fas fa-times"></i> Cancel </a>
                                            @endif
                                            <form action="{{ url('booker/customer-booking/'.$item->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger delete-confirm">
                                                    <i class="far fa-trash-alt"></i> Delete
                                                </button>
                                            </form>

                                        </div>
                                    </div>
                                </td>


                            </tr>
                            @php $number++; @endphp
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

    <script>
        $('.close-booking').click(function () {
            let bookingId = $(this).data('booking-id');

            $.ajax({
                url: '/check-status/' + bookingId,
                type: 'GET',
                success: function (res) {
                    if (res.status === 'pending_payment' || res.status === 'not_received') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Are you sure?',
                            text: 'Do you want to close this booking?',
                            showCancelButton: true,
                            confirmButtonText: 'Haan',
                            cancelButtonText: 'Nahi'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: `Pending amount ${res.amount}`,
                                    text: 'Do You want to Clear it?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Haan',
                                    cancelButtonText: 'Nahi'
                                }).then((paymentResult) => {
                                    if (paymentResult.isConfirmed) {
                                        window.location.href = '/booker/payment/create';
                                    } else {
                                        closeBooking();
                                    }
                                });
                            }
                        });
                        function closeBooking() {
                            $.ajax({
                                url: '/booking/force-close/' + bookingId,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function () {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Booking Closed!',
                                        text: 'Booking successfully closed.',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            });
                        }
                    }


                    else if (res.status === 'deposit_remaining') {
                        Swal.fire({
                            title: 'Deposit Remaining',
                            text: 'Do you want to make a credit note now?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '/booker/credit-note/create?booking_id=' + bookingId;
                            }
                        });
                    }




                    else if(res. status === 'can_close') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Are you sure?',
                            text: 'Do you want to close this booking?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, close it!',
                            cancelButtonText: 'No, cancel!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '/booking/close/' + bookingId,
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function () {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Booking Closed!',
                                            text: 'Booking successfully closed.',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops!',
                                            text: 'Something went wrong while closing the booking.'
                                        });
                                    }
                                });
                            } else {
                                // User ne cancel kiya, koi action nahi
                            }
                        });

                    }
                }
            });
        });

    </script>


@endsection
