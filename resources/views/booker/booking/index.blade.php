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
                                <th>Booking Status</th>
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
                                    @php
                                        $today = \Carbon\Carbon::today();
                                        $bookingStart = \Carbon\Carbon::parse($item->booking->started_at);
                                    @endphp
                                    <td>{{ $number }}.</td>
                                    <td>{{ $item->booking->customer->customer_name ?? 0 }}</td>
                                    <td>{{ $item->booking->agreement_no ?? 0 }}</td>
                                    <td>{{ $item->booking->salePerson->name ?? 'N/A' }}</td>
                                    <td>{{ $item->booking->deposit->deposit_amount ?? 0 }}</td>
                                    <td>{{ $item->booking->booking_status ?? 'overdue' }}</td>
                                    <td>{{ $item->booking->payment->payment_status ?? "pending" }}</td>
                                    <td>
                                        {{-- {{ $item->total_amount }} --}}
                                        @php
                                            $bookingTotal= App\Models\Invoice::where('booking_id', $item->booking->id)->sum('total_amount');
                                        @endphp
                                        {{ $bookingTotal }}
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">

                                                <button class="dropdown-item close-booking" data-booking-id="{{ $item->booking->id }}" {{ $item->booking->booking_status=='closed' ? 'disabled' : '' }}>
                                                    <i class="fas fa-lock"></i> Close Booking
                                                </button>
                                                @if ($item->booking->payment && $item->booking->payment->pending_amount!=0)
                                                <a class="dropdown-item" href="{{ url('booker/payment/create?booking_id='.$item->booking->id) }}"> <i class="far fa-edit"></i> Pending Payment </a>
                                                @endif

                                                <a class="dropdown-item" href="{{ url('booker/booking/'. $item->booking->id) }}"> <i class="fas fa-eye"></i> View </a>
                                                @if (is_null($item->booking->started_at) || (\Carbon\Carbon::parse($item->booking->started_at)->isAfter($now) && $item->booking_cancel==0))
                                                <a class="dropdown-item booking_cancel" data-booking-id="{{ $item->booking->id }}" href=""> <i class="fas fa-times"></i> Cancel </a>
                                                @endif

                                                <a class="dropdown-item" href="{{ url('booker/customer-booking/'.$item->id.'/edit') }}"> <i class="far fa-edit"></i> Edit </a>
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
  <div class="modal-dialog modal-xl" role="document">
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


        // // Update Booking Line Items in BookingData table
        // $(document).on('submit', '#partialBookingForm', function(e){
        //     e.preventDefault();
        //     // Clone the form and remove rows that don't have .bookingDataID.updated
        //     const formClone = $('#partialBookingForm').clone();
        //     formClone.find('.bookingDataID').not('.updated').each(function () {
        //         $(this).closest('tr').next().remove(); // remove paired second row
        //         $(this).closest('tr').remove();        // remove this row
        //     });

        //     let formData = formClone.serialize();
        //     // let formData= $(this).serialize();
        //     $.ajax({
        //         url: '/booking-convert-partial',
        //         type: 'post',
        //         data: formData,
        //         success:function(response){
        //             console.log(response);
        //             $('#activeBookingModal').modal('hide');
        //             Swal.fire({
        //                 icon: 'success',
        //                 title: 'Processing...',
        //                 text: 'Your request has been queued and will be updated in Zoho shortly!',
        //                 confirmButtonText: 'OK'
        //             });
        //         }
        //     });
        // });


        $('#partialBookingForm').on('submit', function(e) {
            e.preventDefault();

            const formClone = $('#partialBookingForm').clone();
            formClone.find('.bookingDataID').not('.updated').each(function () {
                $(this).closest('tr').next().remove();
                $(this).closest('tr').remove();
            });

            let formData = formClone.serialize();

            // Hide modal *immediately*
            $('#activeBookingModal').modal('hide');

            // Show sweet alert
            Swal.fire({
                icon: 'success',
                title: 'Invoice is being updated...',
                text: 'The data has been submitted and is being processed in background.',
                timer: 3000,
                showConfirmButton: false
            });

            $.ajax({
                url: '/booking-convert-partial',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log("Submitted successfully");
                    // Optionally handle further updates
                }
            });
        });

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

                        // Render Rent Details Section in Modal
                        $.each(response.rent_details, function(index, item) {
                            const formattedEndDate = item.end_date.split(' ')[0];
                            const formattedStartDate = item.start_date.split(' ')[0];
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
                                            <label>Total Rent Days</label><br>
                                            <input type="text" value="${item.total_rent_days}" class="form-control total_rent_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Gross Amount</label><br>
                                            <input type="text" value="${item.gross_rent_amount}" class="form-control gross_rent_amount" disabled>
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
                                            <label>Start Date</label><br>
                                            <input type="date" value="${formattedStartDate}" name="" class="form-control" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Return Date</label><br>
                                            <input type="date" value="${formattedEndDate}" name="end_date[]" class="form-control" data-original-end="${formattedEndDate}" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: #fcfcfc;">
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Less Days</label><br>
                                            <input type="text" value="" class="form-control less_days">
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Used Days</label><br>
                                            <input type="text" value="" class="form-control use_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Less Amount</label><br>
                                            <input type="number" value="" class="form-control less_rent_amount" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>New Gross Amount</label><br>
                                            <input type="text" value="${item.gross_rent_amount}" name="new_gross_rent_amount[]" class="form-control new_gross_rent_amount" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Tax (%)</label><br>
                                            <input type="text" value="${item.tax_percent}" class="form-control taxPercent" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>New Rent Amount</label><br>
                                            <input type="text" value="${item.rent_amount}" name="new_amount[]" class="form-control new_rent_amount" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: transparent;"><td colspan="4" style="height:20px;"></td></tr>
                            `;
                        });

                        // row+= '<br><tr style="background-color: transparent;"><td colspan="5" style="height:20px; text-align: center;"><h3>Renew Vehicles</h3></td></tr><br>';


                        // Render Renew Details Section in Modal
                        $.each(response.renew_details, function(index, item) {
                            const formattedEndDate = item.end_date.split(' ')[0];
                            const formattedStartDate = item.start_date.split(' ')[0];
                            row += `
                                <tr style="background-color: #fcfcfc;">
                                    <td rowspan="2" class="text-left align-top">
                                        <br><h5>${item.vehicle_name}</h5>
                                        <input type="hidden" value="${item.bookingDataID}" name="bookingDataID[]" class="bookingDataID">
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Remaining Days</label><br>
                                            <input type="number" value="${item.renew_remaining_days}" class="form-control renew_remaining_days" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Total Renew Days</label><br>
                                            <input type="text" value="${item.total_renew_days}" class="form-control total_renew_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Renew Amount</label><br>
                                            <input type="text" value="${item.renew_amount}" class="form-control renew_amount" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Start Date</label><br>
                                            <input type="date" value="${formattedStartDate}" name="" class="form-control" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Return Date</label><br>
                                            <input type="date" value="${formattedEndDate}" name="end_date[]" class="form-control" data-original-end="${formattedEndDate}" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: #fcfcfc;">
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Less Days</label><br>
                                            <input type="text" value="" class="form-control renew_less_days">
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>Used Days</label><br>
                                            <input type="text" value="" class="form-control renew_use_days" disabled>
                                        </div>
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Less Amount</label><br>
                                            <input type="number" value="" class="form-control less_renew_amount" readonly>
                                        </div>
                                    </td>
                                    <td colspan='2' class="align-middle">
                                        <div class="form-group">
                                            <label>New Renew Amount</label><br>
                                            <input type="text" value="${item.renew_amount}" name="new_amount[]" class="form-control new_renew_amount" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background-color: transparent;"><td colspan="4" style="height:20px;"></td></tr>
                            `;
                        });

                        $('#activeBookingContent').append(row);

                        $('#activeBookingContent').on('input', '.less_days, .renew_less_days', function () {
                            const $formGroup = $(this).closest('tr').prev().find('.bookingDataID');
                            $formGroup.addClass('updated'); // Mark this booking as changed
                        });

                        // Less Rent Days Calculation
                        $('#activeBookingContent').on('input', '.less_days', function () {
                            const $row = $(this).closest('tr').prev(); // get the first row of the pair
                            const totalDays = parseFloat($row.find('.total_rent_days').val()) || 0;
                            const rentAmount = parseFloat($row.find('.rent_amount').val()) || 0;
                            const lessDays = parseFloat($(this).val()) || 0;

                            const $secondRow = $(this).closest('tr');
                            const $usedDaysInput = $secondRow.find('.use_days');
                            const $lessAmountInput = $secondRow.find('.less_rent_amount');
                            const $newAmountInput = $secondRow.find('.new_rent_amount');
                            const $endDateInput = $row.find('input[name="end_date[]"]');

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
                            const newAmount = (rentPerDay * usedDays).toFixed(2); // round for cleaner display
                            const lessAmount = Math.round(rentPerDay * lessDays);

                            $usedDaysInput.val(usedDays);
                            $lessAmountInput.val(lessAmount);
                            $newAmountInput.val(newAmount);

                            // NEW: Gross Amount Calculation
                            const grossAmount = parseFloat($row.find('.gross_rent_amount').val()) || 0;
                            const grossPerDay = grossAmount / totalDays;
                            const newGrossAmount = (grossPerDay * usedDays).toFixed(2);

                            $secondRow.find('.new_gross_rent_amount').val(newGrossAmount);


                            // Update end_date[] using data-original-end
                            const originalEndDateStr = $endDateInput.data('original-end');
                            const originalEndDate = new Date(originalEndDateStr);
                            if (!isNaN(originalEndDate.getTime())) {
                                const updatedEndDate = new Date(originalEndDate);
                                updatedEndDate.setDate(updatedEndDate.getDate() - lessDays);
                                const newDateStr = updatedEndDate.toISOString().split('T')[0];
                                $endDateInput.val(newDateStr);
                            }
                        });

                        // Less Renew Days Calculation
                        $('#activeBookingContent').on('input', '.renew_less_days', function () {
                            const $row = $(this).closest('tr').prev(); // get the first row of the pair
                            const totalDays = parseFloat($row.find('.total_renew_days').val()) || 0;
                            const renewAmount = parseFloat($row.find('.renew_amount').val()) || 0;
                            const lessDays = parseFloat($(this).val()) || 0;
                            const $secondRow = $(this).closest('tr');
                            const $usedDaysInput = $secondRow.find('.renew_use_days');
                            const $lessAmountInput = $secondRow.find('.less_renew_amount');
                            const $newAmountInput = $secondRow.find('.new_renew_amount');
                            const $endDateInput = $row.find('input[name="end_date[]"]');

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
                            const renewPerDay = renewAmount / totalDays;
                            const newAmount = Math.round(renewPerDay * usedDays); // round for cleaner display
                            const lessAmount = Math.round(renewPerDay * lessDays);

                            $usedDaysInput.val(usedDays);
                            $lessAmountInput.val(lessAmount);
                            $newAmountInput.val(newAmount);

                            // Update end_date[] using data-original-end
                            const originalEndDateStr = $endDateInput.data('original-end');
                            const originalEndDate = new Date(originalEndDateStr);
                            if (!isNaN(originalEndDate.getTime())) {
                                const updatedEndDate = new Date(originalEndDate);
                                updatedEndDate.setDate(updatedEndDate.getDate() - lessDays);
                                const newDateStr = updatedEndDate.toISOString().split('T')[0];
                                $endDateInput.val(newDateStr);
                            }
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
                                        confirmButtonText: 'Yes',
                                        cancelButtonText: 'No'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            Swal.fire({
                                                title: `Pending amount ${res.amount}`,
                                                text: 'Do You want to Clear it?',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Recieve Remaining Amount',
                                                cancelButtonText: 'Close Booking'
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
