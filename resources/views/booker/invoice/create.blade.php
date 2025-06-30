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

        .table-responsive{
            overflow: scroll;
            white-space: nowrap
        }
    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <form action="{{ url('booker/booking/'.$booking->id.'/create-invoice') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Create Invoice - {{ $booking->customer->customer_name }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Customer <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $booking->customer->customer_name }}" name="customer" class="form-control disableClick" readonly>
                                        <input type="hidden" value="{{ $booking->id }}" name="booking_id" class="form-control disableClick booking_id" readonly>
                                        <input type="hidden" value="{{ $booking->customer->id }}" name="customer_id" class="form-control disableClick" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Items</h4>
                                    <div class="card-header-form">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search">
                                            <div class="input-group-btn">
                                                <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table table-responsive">
                                        <table class="table table-striped" id="vehicleTable">
                                            <thead>
                                                <tr>
                                                    <th>Type <span class="text-danger">*</span></th>
                                                    <th>Start Date <span class="text-danger">*</span></th>
                                                    <th>Return Date <span class="text-danger">*</span></th>
                                                    <th>Vehicle Type <span class="text-danger">*</span></th>
                                                    <th>Vehicle Name <span class="text-danger">*</span></th>
                                                    <th>Description</th>
                                                    <th>Quantity <span class="text-danger">*</span></th>
                                                    <th>Tax (%)</th>
                                                    <th>Price (AED) <span class="text-danger">*</span></th>
                                                    <th>Total Amount</th>
                                                    <th>
                                                        <button type="button" class="btn btn-success btn-md" id="addRow">+</button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="vehicleTableBody">
                                                <tr>

                                                    <!-- Invoice Type -->
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="invoice_type[]" class="form-control select2 invoice_type">
                                                                <option value="">Select Type</option>
                                                                <option value="2">Renew</option>
                                                                <option value="3">Fine</option>
                                                                <option value="4">Salik</option>
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <input type="date" name="booking_date[]" class="form-control datemask booking-date">
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <input type="date" name="return_date[]" class="form-control datemask return-date">
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <select name="vehicletypes[]" class="form-control select2 vehicletypes" disabled required>
                                                                <option value="">Select Vehicle type</option>
                                                                @foreach ($vehicletypes as $vtype)
                                                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <select name="vehicle[]" class="form-control select2 vehicle" required>
                                                                <option value="">Select Vehicle</option>
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <textarea name="description[]" class="form-control description" cols="60" rows="3" style="width:200px;"></textarea>
                                                        </div>
                                                    </td>

                                                    <td style="display: none" class="align-middle investor"></td>
                                                    <td style="display: none" class="align-middle no_plate"></td>
                                                    <td style="display: none" class="align-middle booking_status"></td>
                                                    <td style="display: none" class="align-middle status"></td>

                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" name="quantity[]" value="1" class="form-control quantity">
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <input type="hidden" name="tax_percent[]" class="tax">
                                                        <div class="form-group">
                                                            <select name="tax[]" class="form-control select2 zohotax">
                                                                <option value="">Select Tax</option>
                                                                @foreach ($taxlist['taxes'] as $item)
                                                                    <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}">
                                                                        {{ $item['tax_name'] }} ({{ $item['tax_percentage'] }}%)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" name="price[]" class="form-control price">
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" name="amount[]" class="form-control disableClick amount" disabled>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div> --}}

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Booking Details</h4>
                                    <div class="card-header-form">
                                        <div class="input-group">
                                                <button type="button" class="btn btn-success btn-md" id="addRow">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="lineItemBody" id="lineItemBody">
                                <div class="card">
                                    <div class="card-body lineItem">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="">Type <span class="text-danger">*</span></label><br>
                                                    <select name="invoice_type[]" class="form-control select2 invoice_type">
                                                        <option value="">Select Type</option>
                                                        <option value="2">Renew</option>
                                                        <option value="3">Fine</option>
                                                        <option value="4">Salik</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="">Start Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" name="booking_date[]" class="form-control datemask booking-date" required>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="">Return Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" name="return_date[]" class="form-control datemask return-date" required>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                                    <select name="vehicletypes[]" class="form-control select2 vehicletypes" disabled required>
                                                        <option value="">Select Vehicle type</option>
                                                        @foreach ($vehicletypes as $vtype)
                                                            <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <span class="d-flex justify-content-between">
                                                    <label for="">Vehicle Name<span class="text-danger">*</span></label>
                                                    <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                                                </span>
                                                <select name="vehicle[]" class="form-control select2 vehicle" required>
                                                    <option value="">Select Vehicle</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <textarea name="description[]" class="form-control description" cols="60" rows="3" style="width:200px;"></textarea>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="">Quantity</label><br>
                                                    <input type="number" name="quantity[]" value="1" class="form-control quantity">
                                                </div>
                                            </div>
                                            <div class="col-2">
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
                                            <div class="col-3">
                                                <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                                <input type="number" name="price[]" class="form-control price" required>
                                            </div>
                                            <div class="col-3">
                                                <label for="">Total Amount</label><br>
                                                <input type="number" name="amount[]" class="form-control disableClick amount" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Invoice Notes <span class="text-danger">*</span></label>
                                <textarea name="notes" style="width:100%;"  cols="30" class="form-control" rows="20" required>{{ old('notes', "Thank you for your business.\nDEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.") }}
                                </textarea>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Create Invoice" id="submitBtn"
                                class="btn btn-primary">
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
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            });
        </script>
    @endif

@endsection

@section('script')

    <script>
        $(document).ready(function(){
            $('#addRow').click(function() {
                let newRow = ``;
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#addRow').click(function() {
                let newRow = `
                <tr>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <select name="invoice_type[]" class="form-control select2 invoice_type" id="">
                                <option value="">Select Type</option>
                                <option value="2">Renew</option>
                                <option value="3">Fine</option>
                                <option value="4">Salik</option>
                            </select>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]"class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td>
                        <div class="form-group"><br>
                            <select name="vehicletypes[]" class="form-control select2 vehicletypes" disabled required>
                                <option value="">Select Vehicle type</option>
                                @foreach ($vehicletypes as $vtype)
                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    <td class="text-truncate"><br>
                        <div class="form-group">
                            <select name="vehicle[]" class="form-control select2 vehicle" required>
                                <option value="">Select Vehicle</option>
                            </select>
                        </div>
                    </td>

                    <td class="text-truncate"><br>
                        <div class="form-group">
                            <textarea name="description[]" class="form-control" id="" cols="60" rows="3"></textarea>
                        </div>
                    </td>

                    <td style="display: none" class="align-middle investor"><br></td>

                    <td style="display: none" class="align-middle no_plate"><br></td>

                    <td style="display: none" class="align-middle booking_status"><br></td>

                    <td style="display: none" class="align-middle status"><br></td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="quantity[]" value="1" class="form-control quantity" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="" class="tax">
                        <div class="form-group">
                                <select name="tax[]"
                                class="form-control select2 zohotax">
                                <option value="">Select Tax</option>
                                @foreach ($taxlist['taxes'] as $item)
                                    <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}">
                                        {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="price[]" class="form-control price" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="amount[]" class="form-control amount" disabled>
                        </div>
                    </td>

                    <th><button type="button" class="btn btn-danger btn-md removeRow">X</button></th>
                </tr>`;
                $('#vehicleTableBody').append(newRow);
                $('.select2').select2({
                    width: '100%'
                });
                $('#vehicleTableBody tr:last .invoice_type').trigger('change');
            });

            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                if ($('#vehicleTableBody tr').length == 0) {
                    let defaultRow = `
                    <tr>
                        <td class="align-middle"><br>
                            <div class="form-group">
                                <select name="invoice_type[]" class="form-control select2 invoice_type" id="">
                                    <option value="">Select Type</option>
                                    <option value="2">Renew</option>
                                    <option value="3">Fine</option>
                                    <option value="4">Salik</option>
                                </select>
                            </div>
                        </td>
                        <td class="align-middle"><br>
                            <div class="form-group">
                                <input type="date" value="" name="booking_date[]"class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                            </div>
                        </td>
                        <td class="align-middle"><br>
                            <div class="form-group">
                                <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                            </div>
                        </td>
                        <td>
                            <div class="form-group"><br>
                                <select name="vehicletypes[]" class="form-control select2 vehicletypes" disabled required>
                                    <option value="">Select Vehicle type</option>
                                    @foreach ($vehicletypes as $vtype)
                                        <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>

                        <td class="text-truncate"><br>
                            <div class="form-group">
                                <select name="vehicle[]" class="form-control select2 vehicle" required>
                                    <option value="">Select Vehicle</option>
                                </select>
                            </div>
                        </td>

                        <td class="text-truncate"><br>
                            <div class="form-group">
                                <textarea name="description[]" class="form-control" id="" cols="60" rows="3"></textarea>
                            </div>
                        </td>

                        <td style="display: none" class="align-middle investor"><br>
                        </td>

                        <td style="display: none" class="align-middle no_plate"><br>
                        </td>

                        <td style="display: none" class="align-middle booking_status"><br>
                        </td>

                        <td style="display: none" class="align-middle status"><br>
                        </td>

                        <td class="align-middle"><br>
                            <div class="form-group">
                                <input type="number" value="" name="quantity[]" value="1" class="form-control quantity" >
                            </div>
                        </td>
                        <td class="align-middle"><br>
                            <input type="hidden" name="tax_percent[]" value="" class="tax">
                            <div class="form-group">
                                    <select name="tax[]"
                                    class="form-control select2 zohotax">
                                    <option value="">Select Tax</option>
                                    @foreach ($taxlist['taxes'] as $item)
                                        <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}">
                                            {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td class="align-middle"><br>
                            <div class="form-group">
                                <input type="number" value="" name="price[]" class="form-control price" >
                            </div>
                        </td>
                        <td class="align-middle"><br>
                            <div class="form-group">
                                <input type="number" value="" name="amount[]" class="form-control amount" disabled>
                            </div>
                        </td>

                        <th><button type="button" class="btn btn-danger btn-md removeRow">X</button></th>
                    </tr>`;
                    $("#vehicleTableBody").append(defaultRow);
                    $('.select2').select2({
                        width: '100%'
                    });
                }
            });

            // Listen for changes using class selectors
            $('table').on('change', '.booking-date, .return-date', function () {
                let row = $(this).closest('tr');
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
            $(document).on('change', '.invoice_type', function() {
                const row = $(this).closest('tr');
                const dateInputs = row.find('.datemask');
                const vehicleType = row.find('.vehicletypes');

                if ($(this).val() === '2') {
                    dateInputs.prop('required', true);
                    dateInputs.prop('disabled', false);
                    vehicleType.prop('disabled', true);
                } else {
                    dateInputs.prop('required', false);
                    dateInputs.prop('disabled', true);
                    vehicleType.prop('disabled', false);
                    dateInputs.val('');

                }
            });

            $('.invoice_type').trigger('change');

            $(document).on('change', '.vehicletypes', function() {
                let id = $(this).val();
                let $row = $(this).closest('tr');
                let bookingId = $('.booking_id').val();
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                let invoiceType = $row.find('.invoice_type').val();
                let bookingDate = $row.find('.booking-date').val();
                let returnDate = $row.find('.return-date').val();

                $vehicleSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: `/get-vehicle-by-booking/${id}/booking/${bookingId}`,
                    type: 'GET',
                    data: {
                        start_date: bookingDate,
                        end_date: returnDate,
                        invoice_type: invoiceType
                    },
                    success: function(response) {
                        $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                        $.each(response, function(key, vehicle) {
                            $vehicleSelect.append(
                                `<option value="${vehicle.id}">${vehicle.number_plate} | ${(vehicle.temp_vehicle_detail ?? vehicle.vehicle_name)}</option>`
                            );
                        });
                    }
                });
            });

            $(document).on('change', '.vehicle', function() {
                let id = $(this).val();
                let row = $(this).closest('tr');
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
                    success: function(response) {
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

            $(document).on('change', '.price, .quantity, .zohotax', function(){
                var row = $(this).closest('tr');
                var zohotax = row.find('.zohotax option:selected').data('percentage') || 0;
                row.find('.tax').val(zohotax);
                var price = parseFloat(row.find('.price').val()) || null;
                var qty = parseFloat(row.find('.quantity').val()) || null;
                var tax = parseFloat(row.find('.tax').val()) || null;
                var subtotal = price*qty;
                var taxAmount= (tax/100) * subtotal;
                var total= subtotal + taxAmount;
                row.find('.amount').val(total);
            });
        });
    </script>
@endsection
