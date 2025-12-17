@extends('admin.master-main')
@section('content')
    <style>
        .disableClick {
            cursor: not-allowed !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__arrow,
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            width: 16px !important;
        }

        .table-responsive {
            overflow: scroll;
            white-space: nowrap
        }

        .header-card h4 {
            font-size: 20px;
            margin-bottom: 0;
        }

        .head-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            text-align: right;
        }
    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <form action="{{ url('booking/' . $booking->id . '/create-invoice') }}" id="" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Create Invoice - {{ $booking->customer->customer_name }}</h4>

                                <div class="form-group m-0" style="width: 250px;">
                                    <label class="m-0">Invoice No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ $code }}" name="code" class="form-control code" required>
                                    <small style="font-size: 16px;" class="code-error"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style=" border-bottom:1px solid #6c757d;">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Customer <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $booking->customer->customer_name }}" name="customer"
                                            class="form-control disableClick" readonly>
                                        <input type="hidden" value="{{ $booking->id }}" name="booking_id"
                                            class="form-control disableClick booking_id" readonly>
                                        <input type="hidden" value="{{ $booking->customer->id }}" name="customer_id"
                                            class="form-control disableClick" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Invoice Date <span class="text-danger">*</span></label>
                                        <input type="date" value="0" name="invoice_date" class="form-control invoice_date"
                                            required>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Due Date <span class="text-danger">*</span></label>
                                        <input type="date" value="0" name="due_date" class="form-control due_date" required>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="header-card head-flex">
                                <h4>Booking Details</h4>
                                <div class="card-header-form">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-success btn-md" id="addRow">+</button>
                                    </div>
                                </div>
                            </div>
                            <hr style=" border-bottom:1px solid #6c757d;">
                            <div class="lineItemBody" id="lineItemBody">
                                <div class="card">
                                    <div class="card-body lineItem">
                                        <div class="close-btn">
                                            <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Type <span class="text-danger">*</span></label><br>
                                                    <select name="invoice_type[]" class="form-control select2 invoice_type">
                                                        <option value="">Select Invoice type</option>
                                                        @foreach ($invoiceTypes as $dtype)
                                                            <option value="{{ $dtype->name }}">{{ $dtype->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>



                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Vehicle Type <span
                                                            class="text-danger">*</span></label><br>
                                                    <select name="vehicletypes[]" class="form-control select2 vehicletypes"
                                                        disabled required>
                                                        <option value="">Select Vehicle type</option>
                                                        @foreach ($vehicletypes as $vtype)
                                                            <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Vehicle Name<span class="text-danger">*</span></label><br>
                                                    <select name="vehicle[]" class="form-control select2 vehicle" required>
                                                        <option value="">Select Vehicle</option>
                                                    </select>
                                                </div>
                                            </div>
                                             <div class="col-3">
                                                <input type="hidden" name="tax_percent[]" class="tax">
                                                <div class="form-group">
                                                    <label for="">Tax (%)</label><br>
                                                    <select name="tax[]" class="form-control select2 zohotax">
                                                        <option value="">Select Tax</option>
                                                        @foreach ($taxlist['taxes'] as $item)
                                                            <option value="{{ $item['tax_id'] }}"
                                                                data-percentage="{{ $item['tax_percentage'] }}">
                                                                {{ $item['tax_name'] }} ({{ $item['tax_percentage'] }}%)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Start Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" name="booking_date[]"
                                                        class="form-control datemask booking-date">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Return Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" name="return_date[]"
                                                        class="form-control datemask return-date">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for=""> DepositType <span
                                                            class="text-danger">*</span></label><br>
                                                    <select name="deposit_type[]" class="form-control select2 deposit_type">
                                                        <option value="">Select Deposit type</option>
                                                        <option value="1">Cardo</option>
                                                        <option value="2">LPO</option>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label>Non Refundable Amount <span class="text-danger">*</span></label>
                                                    <input type="number" value="0" name="non_refundable_amount[]"
                                                        class="form-control non_refundable_amount">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Quantity</label><br>
                                                    <input type="number" name="quantity[]" value="1"
                                                        class="form-control quantity">
                                                </div>
                                            </div>
                                           
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                                    <input type="text" name="price[]" class="form-control price" required>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Discount Amount</label><br>
                                                    <input type="text" name="discount_amount[]" class="form-control discount_amount">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Total Amount</label><br>
                                                    <input type="text" name="amount[]" class="form-control amount" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <textarea name="description[]" class="form-control description"
                                                style="width:100%;height: 100px !important;" cols="60" rows="3"
                                                style="width:200px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Invoice Notes <span class="text-danger">*</span></label>
                                <textarea name="notes" style="width:100%; height: 150px !important;" cols="30"
                                    class="form-control" rows="20"
                                    required>{{ old('notes', "DEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.\nPayment Detail:\nBank Name: WIO\nAccount Name: Rapid Rentals -FZCO\nIBN : AE790860000009637084836\nAccount No: 9637084836\nBIC/SWIFT : WIOBAEADXXX\nQueries: +971 50 366 1754\nComplaints & Suggestions: +971 54 508 2661 or Email: idrees@rapidenterprises.ae") }}</textarea>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Create Invoice" id="submitBtn" class="btn btn-primary">
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...11',
                text: '{{ session('
                                                        error ') }}',
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('
                                                        success ') }}',
            });
        </script>
    @endif

@endsection

@section('script')



    <script>
        $(document).ready(function () {

            // $('#invoice_type').on('change', function() {
            //     var selected = $(this).val().toLowerCase();
            //     alert("jj");
            //     if (selected === 'renew' || selected === 'rent') {
            //         $('#deposit_type_container').show();
            //     } else {
            //         $('#deposit_type_container').hide();
            //     }
            // });

            $('#addRow').click(function () {
                let newRow = `
                                        <div class="card">
                                            <div class="card-body lineItem">
                                                <div class="close-btn">
                                                    <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Type <span class="text-danger">*</span></label><br>
                                                            <select name="invoice_type[]" class="form-control select2 invoice_type">
                                                               <option value="">Select Invoice type</option>
                                                                @foreach ($invoiceTypes as $dtype)
                                                                    <option value="{{ $dtype->name }}">{{ $dtype->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                      <div class="col-3">
                                                    <div class="form-group">
                                                            <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                                            <select name="vehicletypes[]" class="form-control select2 vehicletypes" disabled>
                                                                <option value="">Select Vehicle type</option>
                                                                @foreach ($vehicletypes as $vtype)
                                                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Vehicle Name<span class="text-danger">*</span></label>
                                                            <select name="vehicle[]" class="form-control select2 vehicle">
                                                                <option value="">Select Vehicle</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Tax (%)</label><br>
                                                            <select name="tax[]" class="form-control select2 zohotax">
                                                                <option value="">Select Tax</option>
                                                                @foreach ($taxlist['taxes'] as $item)
                                                                    <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}">
                                                                        {{ $item['tax_name'] }} ({{ $item['tax_percentage'] }}%)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>
                                                <div class="row">

                                                         <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Start Date <span class="text-danger">*</span></label><br>
                                                            <input type="date" name="booking_date[]" class="form-control datemask booking-date">
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Return Date <span class="text-danger">*</span></label><br>
                                                            <input type="date" name="return_date[]" class="form-control datemask return-date">
                                                        </div>
                                                    </div> 
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for=""> DepositType <span class="text-danger">*</span></label><br>
                                                            <select name="deposit_type[]" class="form-control select2 deposit_type">
                                                                <option value="">Select Deposit type</option>
                                                                <option value="1">Cardo</option>
                                                                <option value="2">LPO</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label>Non Refundable Amount <span class="text-danger">*</span></label>
                                                            <input type="number" value="0" name="non_refundable_amount[]" class="form-control non_refundable_amount">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Quantity</label><br>
                                                            <input type="number" name="quantity[]" value="1" class="form-control quantity">
                                                        </div>
                                                    </div>


                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                                            <input type="text" name="price[]" class="form-control price" required>
                                                        </div>  
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Discount Amount</label><br>
                                                            <input type="number" name="discount_amount[]" value="0" class="form-control discount_amount">
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="">Total Amount</label><br>
                                                            <input type="text" name="amount[]" class="form-control amount" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <textarea name="description[]" style="width:100%;height: 100px !important;" class="form-control description" cols="60" rows="3" style="width:200px;"></textarea>
                                                </div>
                                            </div>
                                        </div>`;
                $('#lineItemBody').append(newRow);
                $('.select2').select2({
                    width: '100%'
                });
            });


            $(document).on('click', '.removeRow', function () {
                if ($('.lineItemBody .card').length > 1) {
                    $(this).closest('.card').remove();
                } else {
                    alert("At least one booking box must remain.");
                }
            });

            // Listen for changes using class selectors
            $('#lineItemBody').on('change', '.booking-date, .return-date', function () {
                let row = $(this).closest('.lineItem');
                let bookingDate = row.find('.booking-date').val();
                let returnDate = row.find('.return-date').val();
                let vehicleSelect = row.find('.vehicletypes');

                if (bookingDate && returnDate) {
                    vehicleSelect.prop('disabled', false);
                    vehicleSelect.trigger('change');
                } else {
                    vehicleSelect.prop('disabled', true);
                }
            });

            // Date only enable and disable on invoice type change
            $(document).on('change', '.invoice_type', function () {
                const row = $(this).closest('.lineItem');
                const dateInputs = row.find('.datemask');
                const Vehicle = row.find('.vehicle');
                const nonRefundableAmount = row.find('.non_refundable_amount');
                const depositType = row.find('.deposit_type');
                const vehicleType = row.find('.vehicletypes');

                if ($(this).val() === 'RENEW') {
                    dateInputs.prop('required', true);
                    dateInputs.prop('disabled', false);
                    // Vehicle.prop('disabled', false);
                    depositType.prop('disabled', false);
                    nonRefundableAmount.prop('disabled', false);
                    // vehicleType.prop('disabled', true);
                    $('#deposit_type_container').show();
                    $('#non_refundable_amount').show();


                } else if ($(this).val() === 'Rent') {
                    depositType.prop('disabled', false);
                    nonRefundableAmount.prop('disabled', false);


                } else {
                    dateInputs.prop('required', false);
                    dateInputs.prop('disabled', true);
                    vehicleType.prop('disabled', false);
                    dateInputs.val('');
                    depositType.prop('disabled', true);
                    nonRefundableAmount.prop('disabled', true);

                }


            });

            $('.invoice_type').trigger('change');

            $(document).on('change', '.vehicletypes', function () {
                let id = $(this).val();
                let $row = $(this).closest('.lineItem');
                let bookingId = $('.booking_id').val();
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                let invoiceType = $row.find('.invoice_type').val();
                let bookingDate = $row.find('.booking-date').val();
                let returnDate = $row.find('.return-date').val();
                if (id != "") {

                    $vehicleSelect.empty().append('<option value="">Loading...</option>');
                    $.ajax({
                        url: `/get-vehicle-by-booking/${id}/booking/${bookingId}`,
                        type: 'GET',
                        data: {
                            start_date: bookingDate,
                            end_date: returnDate,
                            invoice_type: invoiceType
                        },
                        success: function (response) {
                            $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                            $.each(response, function (key, vehicle) {
                                $vehicleSelect.append(
                                    `<option value="${vehicle.id}">${vehicle.number_plate} | ${(vehicle.temp_vehicle_detail ?? vehicle.vehicle_name)}</option>`
                                );
                            });
                        }
                    });
                }
            });

            $(document).on('change', '.vehicle', function () {
                let id = $(this).val();
                let row = $(this).closest('.lineItem');
                let no_plate = row.find('.no_plate');
                let investor = row.find('.investor');
                let booking_status = row.find('.booking_status');
                let status = row.find('.status');
                if (!id) {
                    no_plate.val('');
                    booking_status.val('')
                    status.val('');
                    investor.val('');
                    return;
                }
                $.ajax({
                    url: `/get-vehicle-detail/${id}`,
                    type: 'GET',
                    success: function (response) {
                        if (response && Object.keys(response).length > 0) {
                            investor.text(response.investor ?? '');
                            no_plate.text(response.number_plate ?? '');
                            booking_status.text(response.vehicle_status ?? '');
                            status.text(response.status ?? '');
                        } else {

                        }
                    }
                });
            });

            function calculateAmount(row) {
                var taxVal = row.find('.zohotax').val();
                var zohotax = row.find('.zohotax option[value="' + taxVal + '"]').data('percentage') || 0;
                row.find('.tax').val(zohotax);

                var qty = parseFloat(row.find('.quantity').val()) || 1;
                var price = parseFloat(row.find('.price').val()) || 0;
                var discount_amount = parseFloat(row.find('.discount_amount').val()) || 0;
                var grossPrice = price * qty;
                var taxAmount = (zohotax / 100) * grossPrice;
                var total = grossPrice + taxAmount - discount_amount;

                row.find('.amount').val(total.toFixed(2));
            }

            $(document).on('keyup', '.price', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });

             $(document).on('keyup', '.discount_amount', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });

            $(document).on('change', '.zohotax', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });

            $(document).on('change', '.quantity', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });



        });
    </script>


@endsection