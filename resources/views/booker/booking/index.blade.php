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
                        <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
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
                            <th>Status</th>
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
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $bookingStart = \Carbon\Carbon::parse($item->started_at);
                                    $firstInvoice = $item->invoice->first();
                                @endphp
                                <td>{{ $item->id }}.</td>
                                <td>{{ $item->customer->customer_name ?? 0 }}</td>
                                <td>{{ $item->agreement_no ?? 0 }}</td>
                                <td>{{ $item->salePerson->name ?? 'N/A' }}</td>
                                <td>{{ $item->deposit->deposit_amount ?? 0 }}</td>
                                <td>{{ $item->booking_status ?? 'overdue' }}</td>
                                <td>{{ $item->payment->payment_status ?? "pending" }}</td>
                                <td>{{ $firstInvoice->total_amount }}</td>
                                <td class="booking_cancel">  @if ($item->booking_cancel) Cancelled @elseif ($bookingStart->gt($today)) Upcoming @else Active @endif</td>
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
                                            @if (is_null($item->started_at) || (\Carbon\Carbon::parse($item->started_at)->isAfter($now) && $item->booking_cancel==0))
                                            <a class="dropdown-item booking_cancel" data-booking-id="{{ $item->id }}" href=""> <i class="fas fa-times"></i> Cancel </a>
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

<!-- Modal -->
<div class="modal fade" id="activeBookingModal" tabindex="-1" role="dialog" aria-labelledby="activeBookingLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Active Booking Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <form id="partialBookingForm">
                <table class="table">
                    <tbody id="activeBookingContent"></tbody>
                </table>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
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

    </script>

    <script>
        $('.close-booking').click(function () {
            let bookingId = $(this).data('booking-id');
            $.ajax({
                url: '/check-bookingis-active/' + bookingId,
                type: 'GET',
                success: function (response) {
                    if(response.is_active==true){
                        $('#activeBookingModal').modal('show');
                        $('#activeBookingContent').html('');
                        let row= '';
                        $.each(response.rent_details, function(index, item) {
                            const formattedEndDate = item.end_date.split(' ')[0];
                            row += `
                                <tr style="background-color: #fcfcfc;">
                                    <td rowspan="2" class="text-left align-top">
                                        <br><h5>${item.vehicle_name}</h5>
                                        <input type="hidden" value="${item.bookingDataID}" name="bookingDataID[]" class="bookingDataID">
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Remaining Days</label><br>
                                            <input type="number" value="${item.rent_remaining_days}" class="form-control rent_remaining_days" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Total Days</label><br>
                                            <input type="text" value="${item.total_rent_days}" class="form-control total_rent_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Rent Amount</label><br>
                                            <input type="text" value="${item.rent_amount}" class="form-control rent_amount" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Return Date</label><br>
                                            <input type="date" value="${formattedEndDate}" name="end_date[]" class="form-control" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: #fcfcfc;">
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Less Days</label><br>
                                            <input type="text" name="less_days" value="" class="form-control less_days">
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Used Days</label><br>
                                            <input type="text" name="use_days" value="" class="form-control use_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Less Amount</label><br>
                                            <input type="number" value="" name="less_rent_amount" class="form-control less_rent_amount" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>New Amount</label><br>
                                            <input type="text" value="${item.rent_amount}" name="new_rent_amount[]" class="form-control new_rent_amount" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: transparent;"><td colspan="4" style="height:20px;"></td></tr>
                            `;
                        });
                        $('#activeBookingContent').append(row);

                        $('#activeBookingContent').on('input', '.less_days', function () {
                            const $row = $(this).closest('tr').prev(); // get the first row of the pair
                            const totalDays = parseFloat($row.find('.total_rent_days').val()) || 0;
                            const rentAmount = parseFloat($row.find('.rent_amount').val()) || 0;
                            const lessDays = parseFloat($(this).val()) || 0;

                            const $secondRow = $(this).closest('tr');
                            const $usedDaysInput = $secondRow.find('.use_days');
                            const $lessAmountInput = $secondRow.find('.less_rent_amount');
                            const $newAmountInput = $secondRow.find('.new_rent_amount');

                            // Validation: lessDays should not exceed totalDays
                            if (lessDays > totalDays) {
                                $(this).addClass('is-invalid'); // Bootstrap red border
                                $usedDaysInput.val('');
                                $lessAmountInput.val('');
                                $newAmountInput.val('');
                                return;
                            } else {
                                $(this).removeClass('is-invalid');
                            }

                            const usedDays = totalDays - lessDays;
                            const rentPerDay = rentAmount / totalDays;
                            const newAmount = Math.round(rentPerDay * usedDays); // round for cleaner display
                            const lessAmount = Math.round(rentPerDay * lessDays);

                            $usedDaysInput.val(usedDays);
                            $lessAmountInput.val(lessAmount);
                            $newAmountInput.val(newAmount);
                        });

                        $(document).on('submit', '#partialBookingForm', function(e){
                            e.preventDefault();
                            let formData= $(this).serialize();
                            $.ajax({
                                url: '/booking-convert-partial',
                                type: 'post',
                                data: formData,
                                success:function(response){
                                    console.log(response);
                                }
                            });
                        });

                    } else {
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
                                                confirmButtonText: 'Yes',
                                                cancelButtonText: 'No'
                                            }).then((paymentResult) => {
                                                if (paymentResult.isConfirmed) {
                                                    window.location.href = '/booker/payment/create?booking_id='+bookingId;
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
                    }
                }
            });

            // $.ajax({
            //     url: '/check-status/' + bookingId,
            //     type: 'GET',
            //     success: function (res) {
            //         if (res.status === 'pending_payment' || res.status === 'not_received') {
            //             Swal.fire({
            //                 icon: 'warning',
            //                 title: 'Are you sure?',
            //                 text: 'Do you want to close this booking?',
            //                 showCancelButton: true,
            //                 confirmButtonText: 'Haan',
            //                 cancelButtonText: 'Nahi'
            //             }).then((result) => {
            //                 if (result.isConfirmed) {
            //                     Swal.fire({
            //                         title: `Pending amount ${res.amount}`,
            //                         text: 'Do You want to Clear it?',
            //                         icon: 'warning',
            //                         showCancelButton: true,
            //                         confirmButtonText: 'Haan',
            //                         cancelButtonText: 'Nahi'
            //                     }).then((paymentResult) => {
            //                         if (paymentResult.isConfirmed) {
            //                             window.location.href = '/booker/payment/create?booking_id='+bookingId;
            //                         } else {
            //                             closeBooking();
            //                         }
            //                     });
            //                 }
            //             });
            //             function closeBooking() {
            //                 $.ajax({
            //                     url: '/booking/force-close/' + bookingId,
            //                     type: 'POST',
            //                     data: {
            //                         _token: '{{ csrf_token() }}'
            //                     },
            //                     success: function () {
            //                         Swal.fire({
            //                             icon: 'success',
            //                             title: 'Booking Closed!',
            //                             text: 'Booking successfully closed.',
            //                             confirmButtonText: 'OK'
            //                         }).then(() => {
            //                             location.reload();
            //                         });
            //                     }
            //                 });
            //             }
            //         }


            //         else if (res.status === 'deposit_remaining') {
            //             Swal.fire({
            //                 title: 'Deposit Remaining',
            //                 text: 'Do you want to make a credit note now?',
            //                 icon: 'warning',
            //                 showCancelButton: true,
            //                 confirmButtonText: 'Yes',
            //                 cancelButtonText: 'No'
            //             }).then((result) => {
            //                 if (result.isConfirmed) {
            //                     window.location.href = '/booker/credit-note/create?booking_id=' + bookingId;
            //                 }
            //             });
            //         }




            //         else if(res. status === 'can_close') {
            //             Swal.fire({
            //                 icon: 'warning',
            //                 title: 'Are you sure?',
            //                 text: 'Do you want to close this booking?',
            //                 showCancelButton: true,
            //                 confirmButtonText: 'Yes, close it!',
            //                 cancelButtonText: 'No, cancel!'
            //             }).then((result) => {
            //                 if (result.isConfirmed) {
            //                     $.ajax({
            //                         url: '/booking/close/' + bookingId,
            //                         type: 'POST',
            //                         data: {
            //                             _token: '{{ csrf_token() }}'
            //                         },
            //                         success: function () {
            //                             Swal.fire({
            //                                 icon: 'success',
            //                                 title: 'Booking Closed!',
            //                                 text: 'Booking successfully closed.',
            //                                 confirmButtonText: 'OK'
            //                             }).then(() => {
            //                                 location.reload();
            //                             });
            //                         },
            //                         error: function() {
            //                             Swal.fire({
            //                                 icon: 'error',
            //                                 title: 'Oops!',
            //                                 text: 'Something went wrong while closing the booking.'
            //                             });
            //                         }
            //                     });
            //                 } else {
            //                     // User ne cancel kiya, koi action nahi
            //                 }
            //             });

            //         }
            //     }
            // });
        });

    </script>


@endsection
