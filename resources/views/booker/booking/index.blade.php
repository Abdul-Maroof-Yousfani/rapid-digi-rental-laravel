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
                            <th>Status</th>
                            <th>Total Price</th>
                            <th>Date</th>
                            <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $number=1; @endphp
                            @foreach ($booking as $item)
                            <tr>
                                @php $firstInvoice = $item->invoice->first(); @endphp
                                <td>{{ $item->id }}.</td>
                                <td>{{ $item->customer->customer_name ?? 0 }}</td>
                                <td>{{ $item->agreement_no ?? 0 }}</td>
                                <td>{{ $item->salePerson->name ?? 'N/A' }}</td>
                                <td>{{ $item->deposit->deposit_amount ?? 0 }}</td>
                                <td>{{ $item->booking_status ?? 'overdue' }}</td>
                                <td>{{ $firstInvoice->total_amount }}</td>
                                <td>{{ $item->created_at->format('d-M-Y') }}</td>
                                <td>
                                    {{-- <a href="{{ url('booker/booking-close/'. $item->id) }}" class="btn btn-success btn-sm booking-close"><i class="fas fa-lock"></i> Close</a> --}}

                                    <button class="btn btn-success close-booking btn-sm" data-booking-id="{{ $item->id }}" {{ $item->booking_status=='closed' ? 'disabled' : '' }}>
                                        <i class="fas fa-lock"></i> Close Booking
                                    </button>


                                    <a href="{{ url('booker/booking/'. $item->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View</a>
                                    <a href='{{ url("booker/customer-booking/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                    <form action="{{ url("booker/customer-booking/".$item->id."") }}" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                                    </form>
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


    <!-- Modal -->
    <div class="modal fade" id="confirmCloseModal" tabindex="-1" aria-labelledby="confirmCloseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Pending Amount Confirmation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalMessage">Kya aap remaining amount clear karna chahtay hain?</div>
        <div class="modal-footer">
            <a href="#" class="btn btn-success" id="yesRedirectBtn">Yes</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="noCloseBtn">No</button>
        </div>
        </div>
    </div>
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


        document.addEventListener('DOMContentLoaded', function () {
            const closeButtons = document.querySelectorAll('.booking-close');
            closeButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This booking will be closed and cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Close it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
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
                url: '/booker/booking/check-status/' + bookingId,
                type: 'GET',
                success: function (res) {
                    if (res.status === 'pending_payment') {
                        $('#modalMessage').text(`Pending amount hai Rs. ${res.amount}. Kya aap clear karna chahtay hain?`);
                        $('#yesRedirectBtn').attr('href', '/booker/payment/' + bookingId + '/create');
                        $('#confirmCloseModal').modal('show');

                        $('#noCloseBtn').off('click').on('click', function () {
                            $.ajax({
                                url: '/booker/booking/force-close/' + bookingId,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function () {
                                    // location.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Booking Closed!',
                                        text: 'Booking successfully closed.',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                }
                            });
                        });

                    } else if (res.status === 'deposit_remaining') {
                        alert('Deposit Remaining Amount '+ res.deposit_amount);
                        // window.location.href = '/booker/credit-note/create?booking_id=' + bookingId;
                    } else if (res.status === 'can_close') {
                        $.ajax({
                            url: '/booker/booking/close/' + bookingId,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function () {
                                // location.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Booking Closed!',
                                    text: 'Booking successfully closed.',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            }
                        });
                    }
                }
            });
        });

    </script>


@endsection
