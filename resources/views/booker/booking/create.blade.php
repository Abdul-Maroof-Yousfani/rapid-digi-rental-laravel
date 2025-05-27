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
                <form action="{{ role_base_url('customer-booking') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Create Booking</h3>
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
                                                <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Agreement No. <span class="text-danger">*</span></label>
                                        <input type="text" value="" name="agreement_no" class="form-control agreement_no" >
                                    </div>
                                    <div class="form-group">
                                        <label>Deposit Amount <span class="text-danger">*</span></label>
                                        <input type="number" value="0" name="deposit_amount" class="form-control" >
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
                                <div class="card-body ">
                                    <div class="table table-responsive">
                                        <table class="table table-striped" id="vehicleTable">
                                            <thead>
                                                <tr>
                                                    <th>Vehicle Type <span class="text-danger">*</span></th>
                                                    <th>Vehicle Name <span class="text-danger">*</span></th>
                                                    <th>Description </th>
                                                    <th>Investor</th>
                                                    <th>No. Plate</th>
                                                    <th>Booking Status</th>
                                                    <th>Status</th>
                                                    <th>Start Date <span class="text-danger">*</span></th>
                                                    <th>Return Date <span class="text-danger">*</span></th>
                                                    <th>Tax (%) &nbsp;&nbsp;&nbsp;&nbsp; <span class="text-danger"></span></th>
                                                    <th>Price (AED)<span class="text-danger">*</span></th>
                                                    <th>Total Amount &nbsp;&nbsp;</th>
                                                    <th><button type="button" class="btn btn-success btn-md"
                                                            id="addRow">+</button></th>
                                                </tr>
                                            </thead>
                                            <tbody id="vehicleTableBody">
                                                <tr>
                                                    <td>
                                                        <div class="form-group"><br>
                                                            <select name="vehicletypes[]"
                                                                class="form-control select2 vehicletypes" required>
                                                                <option value="">Select Vehicle type</option>
                                                                @foreach ($vehicletypes as $vtype)
                                                                    <option value="{{ $vtype->id }}">{{ $vtype->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <select name="vehicle[]" class="form-control select2 vehicle"
                                                            required>
                                                                <option value="">Select Vehicle</option>
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <textarea name="description[]" style="width:200px;" class="form-control" id="" cols="60" rows="3"></textarea>
                                                        </div>
                                                    </td>

                                                    <td class="align-middle investor"><br>

                                                    </td>

                                                    <td class="align-middle no_plate"><br>

                                                    </td>

                                                    <td class="align-middle booking_status"><br>

                                                    </td>

                                                    <td class="align-middle status"><br>

                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="" name="booking_date[]"
                                                                class="form-control datemask" placeholder="YYYY/MM/DD" required>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="" name="return_date[]"
                                                                class="form-control datemask" placeholder="YYYY/MM/DD" required>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                    <input type="hidden" name="tax_percent[]" value="" class="tax">
                                                    <div class="form-group">
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
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="number" value="" name="price[]" class="form-control price"  required>
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Notes <span class="text-danger">*</span></label>
                                <textarea name="notes" cols="30" class="form-control" rows="10" required>{{ old('notes', "Thank you for your business.\nDEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.") }}</textarea>
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
        $(document).ready(function() {

            $(document).on('keypress', '.agreement_no', function (e) {
                if (e.key === '-' || e.which === 45) {
                    e.preventDefault();
                }
            });

            $(document).on('keypress', '.price', function (e) {
                if (e.key === '-' || e.which === 45) {
                    e.preventDefault();
                }
            });

            $('#addRow').click(function() {
                let newRow = `
                <tr>
                    <td>
                        <div class="form-group"><br>
                            <select name="vehicletypes[]" class="form-control select2 vehicletypes" required>
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

                    <td class="align-middle investor"><br></td>

                    <td class="align-middle no_plate"><br></td>

                    <td class="align-middle booking_status"><br></td>

                    <td class="align-middle status"><br></td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]"class="form-control datemask" placeholder="YYYY/MM/DD" required>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask" placeholder="YYYY/MM/DD" required>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <select name="tax[]"
                                class="form-control select2 zohotax">
                                <option value="">Select Tax</option>
                                @foreach ($taxlist['taxes'] as $item)
                                    <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}"
                                                                                            {{ $item['tax_name']=='VAT' ? 'selected' : '' }}>
                                        {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="tax_percent[]" value="" class="tax">
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="price[]" class="form-control price" required>
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
            });

            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                if ($('#vehicleTableBody tr').length == 0) {
                    let defaultRow = `
                <tr>
                    <td>
                        <div class="form-group"><br>
                            <select name="vehicletypes[]" class="form-control select2 vehicletypes" required>
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

                    <td class="align-middle investor"><br>
                    </td>

                    <td class="align-middle no_plate"><br>
                    </td>

                    <td class="align-middle booking_status"><br>
                    </td>

                    <td class="align-middle status"><br>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]"class="form-control datemask" placeholder="YYYY/MM/DD" required>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask" placeholder="YYYY/MM/DD" required>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="" class="tax">
                        <div class="form-group">
                            <select name="tax[]"
                                class="form-control select2 zohotax">
                                <option value="">Select Tax</option>
                                @foreach ($taxlist['taxes'] as $item)
                                    <option value="{{ $item['tax_id'] }}" data-percentage="{{ $item['tax_percentage'] }}"
                                                                                        {{ $item['tax_name']=='VAT' ? 'selected' : '' }}>
                                        {{ $item['tax_name'] }} ({{ $item['tax_percentage'].'%' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="price[]" class="form-control price" required>
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

            $(document).on('change', '.vehicletypes', function() {
                let id = $(this).val();
                let $row = $(this).closest('tr');
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                $vehicleSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: '/get-vehicle-by-Type/' + id,
                    type: 'GET',
                    success: function(response) {
                        $vehicleSelect.empty().append(
                            '<option value="">Select Vehicle</option>');
                        $.each(response, function(key, vehicle) {
                            $vehicleSelect.append(
                                '<option value="' + vehicle.id + '">'+vehicle.number_plate+' | ' +
                                (vehicle.temp_vehicle_detail ?? vehicle
                                    .vehicle_name) +
                                '</option>'
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

            $(document).on('change', '.price, .zohotax', function(){
                var row = $(this).closest('tr');
                var zohotax = row.find('.zohotax option:selected').data('percentage') || 0;
                row.find('.tax').val(zohotax);
                var price = parseFloat(row.find('.price').val()) || null;
                var tax = parseFloat(row.find('.tax').val()) || null;
                var taxAmount= (tax/100) * price;
                var total= price + taxAmount;
                row.find('.amount').val(total);
            });



        });
    </script>
@endsection
