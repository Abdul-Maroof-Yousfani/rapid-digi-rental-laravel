@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
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
                            <h3 class="mb-0">Booking List</h3>
                            @can('create booking')
                            <a href="{{ route('customer-booking.create') }}" class="btn btn-primary">
                                Add Booking
                            </a>
                            @endcan
                        </div>
                        {{-- <form action="{{ route('invoices.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label for="xlsx_file">Upload XLSX File</label>
                                <input type="file" name="xlsx_file" id="xlsx_file" accept=".xlsx">
                                @error('xlsx_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit">Upload</button>
                        </form>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif --}}
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
                                            <th>Customer</th>
                                            <th>Invoice</th>
                                            <th>Agreement No.</th>
                                            <th>Sale Person</th>
                                            <th>Deposit</th>
                                            <th>Booking Status</th>
                                            <th>Payment</th>
                                            <th>Total Amount</th>
                                            <th align="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bookingList">
                                        @php
                                        $now = \Carbon\Carbon::now();
                                        $number=1;
                                        @endphp
                                        @foreach ($booking as $item)
                                        <tr>
                                            @php
                                            $today = \Carbon\Carbon::today(); // Day after today

                                            // Default status
                                            $status = 'Draft';

                                            // Get the end_date value
                                            $bookingData = $item->booking->bookingData()->select('end_date')->first();

                                            if ($bookingData) {
                                            $endDate = \Carbon\Carbon::parse($bookingData->end_date); // convert to Carbon

                                            // Only check overdue if status is draft
                                            if ($status === 'Draft' && $endDate->lt($today)) {
                                            $status = 'Overdue';
                                            }
                                            }

                                            $payments = $item->booking->payment_status;
                                            if ($payments->contains('payment_status', 'pending')) {
                                            $paymentStatus = 'Partially Paid';
                                            $status = 'Pending';
                                            } elseif ($payments->contains('payment_status', 'paid')) {
                                            $paymentStatus = 'Paid';
                                            $status = 'Completed';
                                            } else {
                                            $paymentStatus = 'Pending';
                                            }
                                            @endphp

                                            <td>{{ $number }}.</td>
                                            <td>{{ $item->booking->customer->customer_name ?? 0 }}</td>
                                            <td>{{ $item->booking->invoice?->zoho_invoice_number ?? 'N/A' }}</td>
                                            <td>{{ $item->booking->agreement_no ?? 0 }}</td>
                                            <td>{{ $item->booking->salePerson->name ?? 'N/A' }}</td>
                                            <td>{{ $item->booking->deposit->initial_deposit ?? 0 }}</td>
                                            <td>{{ $status }}</td>
                                            <td>{{ $paymentStatus }}</td>
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

                                                        <a class="dropdown-item" href="{{ url('booking/'. $item->booking->id) }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>

                                                        @can('edit booking')
                                                        <a class="dropdown-item close-booking"
                                                            data-booking-id="{{ $item->booking->id }}"
                                                            data-invoice-id="{{ $item->id }}"
                                                            {{ $item->booking->booking_status=='closed' ? 'disabled' : '' }}>
                                                            <i class="fas fa-lock"></i> Close Booking
                                                        </a>

                                                        @if (is_null($item->booking->started_at) || (\Carbon\Carbon::parse($item->booking->started_at)->isAfter($now) && $item->booking_cancel==0))
                                                        <a class="dropdown-item booking_cancel" data-booking-id="{{ $item->booking->id }}" href="">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                        @endif

                                                        @if ($item->booking->booking_status != 'closed')
                                                        <a class="dropdown-item" href="{{ url('customer-booking/'.$item->id.'/edit') }}">
                                                            <i class="far fa-edit"></i> Edit
                                                        </a>
                                                        @endif
                                                        @endcan
                                                        @can('delete booking')
                                                        <form action="{{ url('customer-booking/'.$item->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger delete-confirm">
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
                                    </tbody>
                                </table>
                                {{ $booking->links('pagination::bootstrap-4') }}
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
                        <input type="hidden" name="invoice_id" id="invoice_id" value="">
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

    $(document).ready(function() {
        $('#search').on('keyup', function() {
            let search = $(this).val();
            $('#bookingList').html(`
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            $.ajax({
                url: '/search-booking',
                method: 'get',
                data: {
                    search: search
                },
                success: function(response) {
                    let html = '';
                    let number = 1;
                    if (response.bookings.length > 0) {
                        let can = response.can;

                        $.each(response.bookings, function(index, data) {
                            html += `
                                    <tr data-id="${data.id}">
                                        <td>${number}.</td>
                                        <td>${data.customer.customer_name}</td>
                                        <td>${data.invoice?.zoho_invoice_number ?? ''}</td>
                                        <td>${data.agreement_no ?? ''}</td>
                                        <td>${data.sale_person?.name ?? 'N/A'}</td>
                                        <td>${data.deposit?.deposit_amount ?? 0}</td>
                                        <td>${data.booking_status ?? 'overdue'}</td>
                                        <td>${data.payment?.payment_status ?? "pending"}</td>
                                        <td>${data.total_amount ?? 0}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    ${can.view ? `
                                                    <a class="dropdown-item" href="/booking/${data.id}">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>` : ''}
                                                    ${can.delete ? `
                                                        <form action="/customer-booking/${data.id}" method="POST" class="delete-form">
                                                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="dropdown-item text-danger delete-confirm">
                                                                <i class="far fa-trash-alt"></i> Delete
                                                            </button>
                                                        </form>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                `;
                            number++;

                        });
                    } else {
                        html = `<tr><td colspan="9" class="text-center">No results found</td></tr>`;
                    }

                    $('#bookingList').html(html);
                }
            });
        });
    });


    $('#partialBookingForm').on('submit', function(e) {
        e.preventDefault();

        let isValid = true;
        $('.less_days').removeClass('is-invalid').next('.invalid-feedback').remove();
        $('.less_days').each(function() {
            const $lessDaysInput = $(this);
            const $row = $lessDaysInput.closest('tr').prev();
            const totalDays = parseFloat($row.find('.total_rent_days').val()) || 0;
            const lessDays = parseFloat($lessDaysInput.val()) || 0;

            if (lessDays > totalDays) {
                isValid = false;
                $lessDaysInput.addClass('is-invalid');

                if ($lessDaysInput.next('.invalid-feedback').length === 0) {
                    $lessDaysInput.after('<div class="invalid-feedback">Days are greater than total days</div>');
                }
            }
        });

        if (!isValid) {
            return;
        }

        const formClone = $('#partialBookingForm').clone();
        formClone.find('.bookingDataID').not('.updated').each(function() {
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

    $('.close-booking').click(function() {
        let bookingId = $(this).data('booking-id');
        let invoiceId = $(this).data('invoice-id');
        let setInvoiceId = $("#invoice_id").val(invoiceId);
        $.ajax({
            url: '/check-bookingis-active/' + bookingId,
            type: 'GET',
            success: function(response) {
                if (response.is_active == true) {
                    $('#activeBookingModal').modal('show');
                    $('#activeBookingContent').html('');
                    let row = '';

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
                                            <label>Gross Amount</label><br>
                                            <input type="text" value="${item.gross_renew_amount}" class="form-control gross_renew_amount" disabled>
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
                                    <td class="align-middle">
                                        <div class="form-group">
                                            <label>New Gross Amount</label><br>
                                            <input type="number" value="${item.gross_renew_amount}" name="new_gross_rent_amount[]" class="form-control new_gross_renew_amount" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle p-0">
                                        <div class="form-group">
                                            <label>Tax (%)</label><br>
                                            <input type="number" value="${item.tax_percent}" class="form-control taxPercent" readonly>
                                        </div>
                                    </td>
                                    <td class="align-middle">
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

                    $('#activeBookingContent').on('input', '.less_days, .renew_less_days', function() {
                        const $formGroup = $(this).closest('tr').prev().find('.bookingDataID');
                        $formGroup.addClass('updated'); // Mark this booking as changed
                    });

                    // Less Rent Days Calculation
                    $('#activeBookingContent').on('input', '.less_days', function() {
                        const $row = $(this).closest('tr').prev(); // get the first row of the pair
                        const totalDays = parseFloat($row.find('.total_rent_days').val()) || 0;
                        const rentAmount = parseFloat($row.find('.rent_amount').val()) || 0;
                        const lessDays = parseFloat($(this).val()) || 0;

                        const $secondRow = $(this).closest('tr');
                        const $usedDaysInput = $secondRow.find('.use_days');
                        const $lessAmountInput = $secondRow.find('.less_rent_amount');
                        const $newAmountInput = $secondRow.find('.new_rent_amount');
                        const $endDateInput = $row.find('input[name="end_date[]"]');

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
                    $('#activeBookingContent').on('input', '.renew_less_days', function() {
                        const $row = $(this).closest('tr').prev(); // get the first row of the pair
                        const totalDays = parseFloat($row.find('.total_renew_days').val()) || 0;
                        const renewAmount = parseFloat($row.find('.renew_amount').val()) || 0;
                        const lessDays = parseFloat($(this).val()) || 0;

                        const $secondRow = $(this).closest('tr');
                        const $usedDaysInput = $secondRow.find('.renew_use_days');
                        const $lessAmountInput = $secondRow.find('.less_renew_amount');
                        const $newAmountInput = $secondRow.find('.new_renew_amount');
                        const $endDateInput = $row.find('input[name="end_date[]"]');


                        const usedDays = totalDays - lessDays;
                        const renewPerDay = renewAmount / totalDays;
                        const newAmount = (renewPerDay * usedDays).toFixed(2); // round for cleaner display
                        const lessAmount = Math.round(renewPerDay * lessDays);

                        // const usedDays = totalDays - lessDays;
                        // const renewPerDay = renewAmount / totalDays;
                        // const newAmount = Math.round(renewPerDay * usedDays); // round for cleaner display
                        // const lessAmount = Math.round(renewPerDay * lessDays);

                        $usedDaysInput.val(usedDays);
                        $lessAmountInput.val(lessAmount);
                        $newAmountInput.val(newAmount);

                        // NEW: Gross Amount Calculation
                        const grossAmount = parseFloat($row.find('.gross_renew_amount').val()) || 0;
                        const grossPerDay = grossAmount / totalDays;
                        const newGrossAmount = (grossPerDay * usedDays).toFixed(2);

                        $secondRow.find('.new_gross_renew_amount').val(newGrossAmount);


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
                        success: function(res) {
                            if (res.status === 'pending_payment' || res.status === 'not_received') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Are you sure?',
                                    text: 'Do you want to close this booking?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes',
                                    cancelButtonText: 'No',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        Swal.fire({
                                            title: `Pending amount ${res.amount}`,
                                            text: 'Do You want to Clear it?',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Recieve Remaining Amount',
                                            cancelButtonText: 'Close Booking',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false
                                        }).then((paymentResult) => {
                                            if (paymentResult.isConfirmed) {
                                                window.location.href = '/payment/create?booking_id=' + bookingId;
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
                                        success: function() {
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
                            } else if (res.status === 'deposit_remaining') {
                                Swal.fire({
                                    title: 'Deposit Remaining',
                                    text: 'Do you want to make a credit note now?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes',
                                    cancelButtonText: 'No'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '/credit-note/create?booking_id=' + bookingId;
                                    }
                                });
                            } else if (res.status === 'can_close') {
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
                                            success: function() {
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