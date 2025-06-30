@extends('admin.master-main')
@section('content')
    <style>
 .disableClick{cursor:not-allowed !important;}
.select2-container--default .select2-selection--multiple .select2-selection__arrow,.select2-container--default .select2-selection--single .select2-selection__arrow{width:16px !important;}
.table-responsive{overflow:scroll;white-space:nowrap}
.header-card h4{font-size:20px;margin-bottom:0;}
.head-flex{display:flex;justify-content:space-between;align-items:center;}
.close-btn{text-align:right;}



    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <form action="{{ role_base_url('customer-booking') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="header-card">
                                <h4>Create Booking</h4>
                            </div>
                        </div>
                    </div> 
                    <hr style=" border-bottom:1px solid #6c757d;">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-control select2" required>
                                            <option value="">Select Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Deposit Amount <span class="text-danger">*</span></label>
                                        <input type="number" value="0" name="deposit_amount" class="form-control deposit_amount" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Started At <span class="text-danger">*</span></label>
                                        <input type="date" value="0" name="started_at" class="form-control started_at" required>
                                    </div>
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
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Agreement No. <span class="text-danger">*</span></label>
                                        <input type="text" value="" name="agreement_no" class="form-control agreement_no" required>
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
                                                    <label for="">Start Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" value="" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD" required>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Return Date <span class="text-danger">*</span></label><br>
                                                    <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD" required>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                                    <select name="vehicletypes[]"
                                                        class="form-control select2 vehicletypes" disabled required>
                                                        <option value="">Select Vehicle type</option>
                                                        @foreach ($vehicletypes as $vtype)
                                                            <option value="{{ $vtype->id }}">{{ $vtype->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="">Vehicle Name <span class="text-danger">*</span></label><br>
                                                    <select name="vehicle[]" class="form-control select2 vehicle"required>
                                                        <option value="">Select Vehicle</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                           
                                            <div class="col-3">
                                                  <div class="form-group">
                                                    <label for="">Tax (%)</label><br>
                                                    <select name="tax[]"class="form-control select2 zohotax" readonly>
                                                        <option value="">Select Tax</option>
                                                        @foreach ($taxlist['taxes'] as $item)
                                                            <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}"
                                                                                                                    {{ $item['tax_name']=='VAT' ? 'selected' : '' }}>
                                                                {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                       </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                                <input type="number" value="" name="price[]" class="form-control price"  required>
                                                        </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                <label for="">Total Amount</label><br>
                                                <input type="number" value="" name="amount[]" class="form-control amount" disabled>
                                                        </div>
                                            </div>
                                            
                                        </div>
                                      
                                            <div class="form-group">
                                                <textarea name="description[]" style="width:100%;height: 100px !important;" class="form-control" id="" cols="60" rows="4" placeholder="Description"></textarea>
                                            </div>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Notes <span class="text-danger">*</span></label>
                                <textarea name="notes" cols="30" style="height: 150px !important;"  class="form-control" rows="10" required>{{ old('notes', "Thank you for your business.\nDEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.") }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Create Booking" id="submitBtn"
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
                });
                $('.return-date').each(function () {
                    $(this).attr('min', startedAt);
                });
            }
        }

        // Multiple line item not selected same vehicle
        function updateVehicleOptions() {
            // Step 1: Collect all selected vehicle IDs
            let selectedVehicleIds = [];
            $('select[name="vehicle[]"]').each(function () {
                let val = $(this).val();
                if (val) selectedVehicleIds.push(val);
            });

            // Step 2: Loop over each vehicle dropdown
            $('select[name="vehicle[]"]').each(function () {
                let currentSelect = $(this);
                let currentVal = currentSelect.val();

                // Re-enable all options first
                currentSelect.find('option').prop('disabled', false);

                // Step 3: Disable options that are selected in other rows
                selectedVehicleIds.forEach(function (val) {
                    if (val !== currentVal) {
                        currentSelect.find(`option[value="${val}"]`).prop('disabled', true);
                    }
                });
            });
        }


        $(document).ready(function() {

            // Not Enter Minus Values in These Fields
            $(document).on('keypress', '.agreement_no, .price, .deposit_amount', function (e) {
                if (e.key === '-' || e.which === 45) {
                    e.preventDefault();
                }
            });

            $('.started_at').on('change', function () {
                let startedAt = $(this).val();
                applyMinDateToAllDateFields(startedAt);
            });


            $('#addRow').click(function(){
                let newRow = `
                <div class="card">
                    <div class="card-body lineItem">
                        <div class="close-btn">
                            <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Start Date <span class="text-danger">*</span></label><br>
                                    <input type="date" value="" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD" required>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Return Date <span class="text-danger">*</span></label><br>
                                    <input type="date" value="" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD" required>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                    <select name="vehicletypes[]"
                                        class="form-control select2 vehicletypes" disabled required>
                                        <option value="">Select Vehicle type</option>
                                        @foreach ($vehicletypes as $vtype)
                                            <option value="{{ $vtype->id }}">{{ $vtype->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Vehicle Name<span class="text-danger">*</span></label>
                                    <select name="vehicle[]" class="form-control select2 vehicle"
                                    required>
                                        <option value="">Select Vehicle</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Tax (%)</label><br>
                                    <select name="tax[]"class="form-control select2 zohotax" readonly>
                                        <option value="">Select Tax</option>
                                        @foreach ($taxlist['taxes'] as $item)
                                            <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}"
                                                                                                    {{ $item['tax_name']=='VAT' ? 'selected' : '' }}>
                                                {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                            </option>
                                        @endforeach
                                    </select> 
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                    <input type="number" value="" name="price[]" class="form-control price"  required>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Total Amount</label><br>
                                    <input type="number" value="" name="amount[]" class="form-control amount" disabled>
                                </div>
                            </div>
                        </div>
                         <textarea name="description[]" style="width:100%;height: 100px !important;"  class="form-control" id="" cols="60" rows="4" placeholder="Description"></textarea>
                    </div>
                </div>`;

                $('#lineItemBody').append(newRow);
                $('.select2').select2({
                    width: '100%'
                });

                let startedAt = $('.started_at').val();
                applyMinDateToAllDateFields(startedAt);

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


            $(document).on('change', '.vehicletypes', function() {
                let $row = $(this).closest('.lineItem');
                let typeId = $(this).val();
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                let bookingDate = $row.find('.booking-date').val();
                let returnDate = $row.find('.return-date').val();
                if (!bookingDate || !returnDate) {
                    alert("Please select booking and return date first");
                    return;
                }

                $vehicleSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: `/get-vehicle-by-Type/${typeId}`,
                    type: 'GET',
                    data: {
                        start_date: bookingDate,
                        end_date: returnDate
                    },
                    success: function(response) {
                        $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                        $.each(response, function(key, vehicle) {
                            $vehicleSelect.append(
                                `<option value="${vehicle.id}">${vehicle.number_plate} | ${vehicle.temp_vehicle_detail ?? vehicle.vehicle_name}</option>`
                            );
                        });
                        updateVehicleOptions();
                    }
                });
            });




            $(document).on('change', '.vehicle', function() {
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
                    url: '/get-vehicle-detail/' + id,
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

            function calculateAmount(row) {
                var taxVal = row.find('.zohotax').val();
                var zohotax = row.find('.zohotax option[value="' + taxVal + '"]').data('percentage') || 0;
                row.find('.tax').val(zohotax);

                var price = parseFloat(row.find('.price').val()) || 0;
                var taxAmount = (zohotax / 100) * price;
                var total = price + taxAmount;

                row.find('.amount').val(total.toFixed(2));
            }

            $(document).on('change', '.price', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });

            $(document).on('change', '.zohotax', function () {
                let row = $(this).closest('.lineItem');
                calculateAmount(row);
            });



        });
    </script>
@endsection
