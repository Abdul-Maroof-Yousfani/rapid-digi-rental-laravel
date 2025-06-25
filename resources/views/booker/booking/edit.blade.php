@extends('admin.master-main')
@section('content')
    <style>
 .disableClick{cursor:not-allowed !important;}
.select2-container--default .select2-selection--multiple .select2-selection__arrow,.select2-container--default .select2-selection--single .select2-selection__arrow{width:16px !important;}
.table-responsive{overflow:scroll;white-space:nowrap}
.form-group {
    margin-bottom: 0px !important;
}
    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <form action="{{ role_base_url('customer-booking/'.$invoice->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    {{-- @php $firstInvoice= $booking->invoice->first(); @endphp
                                    @foreach($booking->invoice as $inv)
                                        {{ $inv->booking_id }}<br>
                                    @endforeach --}}
                                    <h3>Edit Booking  #{{ $invoice->zoho_invoice_number }}</h3>
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
                                        <select name="customer_id" class="form-control select2" required>
                                            <option value="">Select Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $invoice->booking->customer_id==$customer->id ? 'selected' : '' }} >{{ $customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Deposit Amount <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $invoice->booking->deposit->deposit_amount ?? 0 }}" name="deposit_amount" class="form-control" >
                                    </div>
                                    <div class="form-group">
                                        <label>Started At <span class="text-danger">*</span></label>
                                        <input type="date" name="started_at" value="{{ \Carbon\Carbon::parse($invoice->booking->started_at)->format('Y-m-d') }}" class="form-control started_at" >
                                    </div>
                                    @if ($invoice->invoice_status=='sent')
                                        <div class="form-group">
                                            <label>Reason For Update. <span class="text-danger">*</span></label>
                                            <input type="text" value="" name="reason" class="form-control" required>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Sale Person <span class="text-danger">*</span></label>
                                        <select name="sale_person_id" class="form-control select2" required>
                                            <option value="">Select Sale Person</option>
                                            @foreach ($salePerson as $item)
                                                <option value="{{ $item->id }}" {{ $item->id==$invoice->booking->sale_person_id ? 'Selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Agreement No. <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $invoice->booking->agreement_no }}" name="agreement_no" class="form-control agreement_no" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Booking Details</h4>
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
                                                    <th>Start Date <span class="text-danger">*</span></th>
                                                    <th>Return Date <span class="text-danger">*</span></th>
                                                    <th>Vehicle Type <span class="text-danger">*</span></th>
                                                    <th>Vehicle Name <span class="text-danger">*</span></th>
                                                    <th>Description </th>
                                                    <th style="display: none">Investor</th>
                                                    <th style="display: none">No. Plate</th>
                                                    <th>Tax (%) &nbsp;&nbsp;&nbsp;&nbsp; <span class="text-danger"></span></th>
                                                    <th>Price (AED)<span class="text-danger">*</span></th>
                                                    <th>Total Amount &nbsp;&nbsp;</th>
                                                    <th><button type="button" class="btn btn-success btn-md"
                                                            id="addRow">+</button></th>
                                                </tr>
                                            </thead>
                                            <tbody id="vehicleTableBody">
                                                @foreach ($booking_data as $index => $item)
                                                @php
                                                    $selectedVehicleId = $item->vehicle_id;
                                                    $selectedTypeId = $vehicleTypeMap[$selectedVehicleId] ?? null;
                                                    $filteredVehicles = $vehiclesByType[$selectedTypeId] ?? collect();
                                                    $vehicleObj = $vehicles->where('id', $selectedVehicleId)->first();
                                                @endphp
                                                <tr>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}" name="booking_date[]"
                                                                class="form-control datemask booking-date" placeholder="YYYY/MM/DD"
                                                                data-default="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}">
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') }}" name="return_date[]"
                                                                class="form-control datemask return-date" placeholder="YYYY/MM/DD"
                                                                data-default="{{ \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group"><br>
                                                            <select name="vehicletypes[]" class="form-control select2 vehicletypes">
                                                                <option value="">Select Vehicle type</option>
                                                                @foreach ($vehicletypes as $vtype)
                                                                    <option value="{{ $vtype->id }}" {{ $selectedTypeId == $vtype->id ? 'selected' : '' }}>
                                                                        {{ $vtype->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    @php
                                                        $selectedVehicleText = ($item->vehicle->vehicle_name) ?? ($item->vehicle->number_plate.' | '.$item->vehicle->temp_vehicle_detail);
                                                    @endphp
                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <select name="vehicle[]" class="form-control select2 vehicle"
                                                                data-selected="{{ $selectedVehicleId }}"
                                                                data-selected-text="{{ $selectedVehicleText }}">
                                                                <option value="">Select Vehicle</option>
                                                                @foreach ($filteredVehicles as $vehicle)
                                                                @php $disable= $vehicle->status==0 ? 'disabled' : ''; @endphp
                                                                    <option value="{{ $vehicle->id }}" {{ $disable }} {{ $selectedVehicleId == $vehicle->id ? 'selected' : '' }}>
                                                                        {{ ($vehicle->vehicle_name) ?? ($vehicle->number_plate.' | '.$vehicle->temp_vehicle_detail) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <textarea name="description[]" style="width:200px;" class="form-control" id="" cols="60" rows="3">
                                                                {{ $zohocolumn['invoice']['line_items'][$index]['description'] ?? '' }}
                                                            </textarea>
                                                        </div>
                                                    </td>

                                                    <td style="display: none" class="align-middle investor"><br>
                                                        {{ $vehicleObj->investor->name ?? 'N/A' }}
                                                    </td>

                                                    <td style="display: none" class="align-middle no_plate"><br>
                                                        {{ $vehicleObj->number_plate ?? 'N/A' }}
                                                    </td>

                                                    <td class="align-middle"><br>
                                                        <input type="hidden" name="tax_percent[]" value="{{ $item->tax_percent }}" class="tax">
                                                        <div class="form-group">
                                                            <select name="tax[]"
                                                                class="form-control select2 zohotax" required>
                                                                <option value="">Select Tax</option>
                                                                @foreach ($taxlist['taxes'] as $taxes)
                                                                    <option value="{{ $taxes['tax_id'] }}"
                                                                    data-percentage="{{ $taxes['tax_percentage'] }}"
                                                                    {{ $zohocolumn['invoice']['line_items'][$index]['tax_id']==$taxes['tax_id']  ? 'Selected' : '' }}>
                                                                        {{ $taxes['tax_name'] }} ({{ $taxes['tax_percentage'].'%' }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="number" value="{{ $item->price }}" name="price[]" class="form-control price" >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="number" value="" name="amount[]" class="form-control amount" disabled>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-danger btn-md removeRow">X</button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Invoice Notes <span class="text-danger">*</span></label>
                                <textarea name="notes" cols="30" class="form-control" rows="10" required>{{ $invoice->booking->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Update Booking" id="submitBtn"
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

        // Line items vehicles booking end date equals to Started at
        function applyMinDateToAllDateFields(startedAt) {
            if (startedAt) {
                $('.booking-date').each(function () {
                    $(this).attr('min', startedAt);

                    // Force reset if current value is less than new min
                    if ($(this).val() < startedAt) {
                        $(this).val('');
                    }
                });
                $('.return-date').each(function () {
                    $(this).attr('min', startedAt);

                    if ($(this).val() < startedAt) {
                        $(this).val('');
                    }
                });
            }
        }

        $(document).ready(function() {

            $(document).on('keypress', '.agreement_no, .price', function (e) {
                if (e.key === '-' || e.which === 45) {
                    e.preventDefault();
                }
            });

                let startedAt = $('.started_at').val();
                applyMinDateToAllDateFields(startedAt);

                $('.started_at').trigger('change');

            $('#addRow').click(function() {
                let newRow = `
                <tr>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td>
                        <div class="form-group"><br>
                            <select name="vehicletypes[]" class="form-control select2 vehicletypes">
                                <option value="">Select Vehicle type</option>
                                @foreach ($vehicletypes as $vtype)
                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    <td class="text-truncate"><br>
                        <div class="form-group">
                            <select name="vehicle[]" class="form-control select2 vehicle">
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

                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="" class="tax">
                        <div class="form-group">
                            <select name="tax[]" class="form-control select2 zohotax">
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
                            <input type="number" value="" name="price[]" class="form-control price">
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

                let startedAt = $('.started_at').val();
                applyMinDateToAllDateFields(startedAt);
            });

            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                if ($('#vehicleTableBody tr').length == 0) {
                    let defaultRow = `
                <tr>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td>
                        <div class="form-group"><br>
                            <select name="vehicletypes[]" class="form-control select2 vehicletypes">
                                <option value="">Select Vehicle type</option>
                                @foreach ($vehicletypes as $vtype)
                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    <td class="text-truncate"><br>
                        <div class="form-group">
                            <select name="vehicle[]" class="form-control select2 vehicle">
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

                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="" class="tax">
                        <div class="form-group">
                            <select name="tax[]"
                                class="form-control select2 zohotax" required>
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
                            <input type="number" value="" name="price[]" class="form-control price">
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

                    let startedAt = $('.started_at').val();
                    applyMinDateToAllDateFields(startedAt);
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

            $(document).on('change', '.vehicletypes', function() {
                let $row = $(this).closest('tr');
                let typeId = $(this).val();
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                let selectedVehicleId = $vehicleSelect.data('selected');
                let selectedVehicleText = $vehicleSelect.data('selected-text') || 'Selected vehicle';
                let defaultStart = $row.find('.booking-date').data('default');
                let defaultEnd = $row.find('.return-date').data('default');

                let bookingID = {{ $invoice->booking->id }};
                let bookingDate = $row.find('.booking-date').val();
                let returnDate = $row.find('.return-date').val();
                if (!bookingDate || !returnDate) {
                    alert("Please select booking and return date first");
                    return;
                }

                let dateChanged = bookingDate !== defaultStart || returnDate !== defaultEnd;

                $vehicleSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: `/get-vehicle-by-Type/${typeId}`,
                    type: 'GET',
                    data: {
                        start_date: bookingDate,
                        end_date: returnDate,
                        bookingID: bookingID,
                        selectedVehicleId: !dateChanged ? selectedVehicleId : null
                    },
                    success: function(response) {
                        let found = false;
                        $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                        $.each(response, function(key, vehicle) {
                            let selected = (vehicle.id == selectedVehicleId) ? 'selected' : '';
                            if (selected) found = true;
                            $vehicleSelect.append(
                                `<option value="${vehicle.id}">${vehicle.number_plate} | ${vehicle.temp_vehicle_detail ?? vehicle.vehicle_name}</option>`
                            );
                        });

                        if (!found && selectedVehicleId && !dateChanged) {
                            $vehicleSelect.append(`<option value="${selectedVehicleId}" selected>${selectedVehicleText} (Booked)</option>`);
                        }
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
                    url: '/get-vehicle-detail/' + id,
                    type: 'GET',
                    success: function(response) {
                        if (response && Object.keys(response).length > 0) {
                            investor.text(response.investor ?? '');
                            no_plate.text(response.number_plate ?? '');
                            booking_status.text(response.booking_status ?? '');
                            status.text(response.status ?? '');

                            investor.val(response.investor ?? '');
                            no_plate.val(response.number_plate ?? '');
                            booking_status.val(response.booking_status ?? '');
                            status.val(response.status ?? '');
                        } else {
                            no_plate.val('');
                            booking_status.val('');
                            status.val('');
                        }
                    }
                });
            });

            // $(document).on('change', '.price, .zohotax', function(){
            //     var row = $(this).closest('tr');
            //     var zohotax = row.find('.zohotax option:selected').data('percentage') || 0;
            //     row.find('.tax').val(zohotax);
            //     var price = parseFloat(row.find('.price').val()) || null;
            //     var tax = parseFloat(row.find('.tax').val()) || null;
            //     var taxAmount = (tax/100) * price;
            //     var total = price + taxAmount;
            //     row.find('.amount').val(total);
            // });


        });
        $(document).on('change', '.price, .zohotax', function(){
            var row = $(this).closest('tr');
            calculateRowTotal(row);
        });
        function calculateRowTotal(row) {
            var zohotax = row.find('.zohotax option:selected').data('percentage') || 0;
            row.find('.tax').val(zohotax);
            var price = parseFloat(row.find('.price').val()) || null;
            var tax = parseFloat(row.find('.tax').val()) || null;
            var taxAmount = (tax/100) * price;
            var total = price + taxAmount;
            row.find('.amount').val(total);
        }

        $(document).ready(function () {
            $('tr').each(function () {
                calculateRowTotal($(this));
            });
        });
    </script>
@endsection
