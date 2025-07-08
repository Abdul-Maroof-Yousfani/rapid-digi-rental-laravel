@extends('admin.master-main')
@section('content')
    <style>
        .disableClick{cursor:not-allowed !important;}
        .select2-container--default .select2-selection--multiple .select2-selection__arrow,.select2-container--default .select2-selection--single .select2-selection__arrow{width:16px !important;}
        .table-responsive{overflow:scroll;white-space:nowrap}
    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <form action="{{ url('booking/'. $invoice->id .'/update-invoice') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    @php
                                        $customer_name= $invoice->booking->customer->customer_name;
                                        $customer_id= $invoice->booking->customer->id;
                                        $booking_id= $invoice->booking->id;
                                    @endphp
                                    <h3>Edit Invoice #{{ $invoice->zoho_invoice_number }}</h3>
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
                                        <input type="text" value="{{ $customer_name }}" name="customer" class="form-control disableClick" readonly>
                                        <input type="hidden" value="{{ $booking_id }}" name="booking_id" class="form-control disableClick booking_id" readonly>
                                        <input type="hidden" value="{{ $customer_id }}" name="customer_id" class="form-control disableClick" readonly>
                                    </div>

                                </div>
                            </div>
                        </div>

                        @if ($invoice->invoice_status=='sent')
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                        <div class="form-group">
                                            <label>Reason For Update. <span class="text-danger">*</span></label>
                                            <input type="text" value="" name="reason" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                                    <th style="display: none;">Investor</th>
                                                    <th style="display: none;">No. Plate</th>
                                                    <th style="display: none;">Booking Status</th>
                                                    <th style="display: none;">Status</th>
                                                    <th>Start Date <span class="text-danger">*</span></th>
                                                    <th>Return Date <span class="text-danger">*</span></th>
                                                    <th>Type <span class="text-danger">*</span></th>
                                                    <th>Quantity <span class="text-danger">*</span></th>
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
                                                    $vehicleObj = $vehicles->where('id', $selectedVehicleId)->first();
                                                    $selectedVehicleTypeId = $vehicleObj->vehicletypes;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="form-group"><br>
                                                            <select name="vehicletypes[]" class="form-control select2 vehicletypes">
                                                                <option value="">Select Vehicle type</option>
                                                                @foreach ($vehicletypes as $vtype)
                                                                    <option value="{{ $vtype->id }}" {{ $vtype->id == $selectedVehicleTypeId ? 'Selected' : '' }}>{{ $vtype->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <select name="vehicle[]" class="form-control select2 vehicle">
                                                                <option value="">Select Vehicle</option>
                                                                @foreach ($vehicles as $vehicle)
                                                                    <option value="{{ $vehicle->id }}" {{ $vehicle->id == $selectedVehicleId ? 'Selected' : '' }}>{{ $vehicle->number_plate }} | {{ $vehicle->temp_vehicle_detail ?? $vehicle->vehicle_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="text-truncate"><br>
                                                        <div class="form-group">
                                                            <textarea name="description[]" style="width:200px;"class="form-control" id="" cols="60" rows="3">

                                                            </textarea>
                                                        </div>
                                                    </td>

                                                    <td style="display: none;" class="align-middle investor"><br>
                                                        {{ $vehicleObj->investor->name ?? 'N/A' }}
                                                    </td>

                                                    <td style="display: none;" class="align-middle no_plate"><br>
                                                        {{ $vehicleObj->number_plate ?? 'N/A' }}
                                                    </td>

                                                    <td style="display: none;" class="align-middle booking_status"><br>
                                                        {{ $vehicleObj->vehiclestatus->name }}
                                                    </td>

                                                    <td style="display: none;" class="align-middle status"><br>
                                                        {{ $vehicleObj->status === null ? 'N/A' :  ($vehicleObj->status==1 ? 'Active' : 'Inactive') }}
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d-M-Y') : '' }}" name="booking_date[]"
                                                                class="form-control datemask" placeholder="YYYY/MM/DD"
                                                                >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d-M-Y') : '' }}" name="return_date[]"
                                                                class="form-control datemask" placeholder="YYYY/MM/DD"
                                                                >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <select name="invoice_type[]" class="form-control select2 invoice_type" id="">
                                                                <option value="">Select Type</option>
                                                                <option value="2" {{ $item->transaction_type == 2 ? 'Selected' : '' }}>Renew</option>
                                                                <option value="3" {{ $item->transaction_type == 3 ? 'Selected' : '' }}>Fine</option>
                                                                <option value="4" {{ $item->transaction_type == 4 ? 'Selected' : '' }}>Salik</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="number" value="{{ $zohocolumn['invoice']['line_items'][$index]['quantity'] ?? '' }}" name="quantity[]" class="form-control quantity" >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <input type="hidden" name="tax_percent[]" value="{{ $zohocolumn['invoice']['line_items'][$index]['tax_percentage'] ?? '' }}" class="tax">
                                                        <div class="form-group">
                                                                <select name="tax[]"
                                                                class="form-control select2 zohotax">
                                                                <option value="">Select Tax</option>
                                                                @foreach ($taxlist['taxes'] as $taxes)
                                                                    <option value="{{ $taxes['tax_id'] }}"
                                                                    {{ $zohocolumn['invoice']['line_items'][$index]['tax_id']==$taxes['tax_id'] ? 'Selected' : '' }}
                                                                    data-percentage="{{ $taxes['tax_percentage'] }}">
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
                                <textarea name="notes" cols="30" class="form-control" rows="10" required>{{ old('notes', "Thank you for your business.\nDEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.") }}
                                </textarea>
                            </div>
                        </div>
                    </div>
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
        $(document).ready(function() {
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
                            <textarea name="description[]" style="width:200px;"class="form-control" id="" cols="60" rows="3"></textarea>
                        </div>
                    </td>

                    <td style="display: none;" class="align-middle investor"><br></td>

                    <td style="display: none;" class="align-middle no_plate"><br></td>

                    <td style="display: none;" class="align-middle booking_status"><br></td>

                    <td style="display: none;" class="align-middle status"><br></td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]"class="form-control datemask" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <select name="invoice_type[]" class="form-control select2 invoice_type" id="">
                                <option value="">Select Type</option>
                                <option value="2">Renew</option>
                                {{-- <option value="1">Rent</option> --}}
                                <option value="3">Fine</option>
                                <option value="4">Salik</option>
                            </select>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="quantity[]" class="form-control quantity" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="{{ $zohocolumn['invoice']['line_items'][$index]['tax_percentage'] ?? '' }}" class="tax">
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
            });

            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                if ($('#vehicleTableBody tr').length == 0) {
                    let defaultRow = `
                <tr>
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

                    <td style="display: none;" class="align-middle investor"><br>
                    </td>

                    <td style="display: none;" class="align-middle no_plate"><br>
                    </td>

                    <td style="display: none;" class="align-middle booking_status"><br>
                    </td>

                    <td style="display: none;" class="align-middle status"><br>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="booking_date[]"class="form-control datemask" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="date" value="" name="return_date[]" class="form-control datemask" placeholder="YYYY/MM/DD">
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <select name="invoice_type[]" class="form-control select2 invoice_type" id="">
                                <option value="">Select Type</option>
                                <option value="2">Renew</option>
                                {{-- <option value="1">Rent</option> --}}
                                <option value="3">Fine</option>
                                <option value="4">Salik</option>
                            </select>
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="quantity[]" class="form-control quantity" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <input type="hidden" name="tax_percent[]" value="{{ $zohocolumn['invoice']['line_items'][$index]['tax_percentage'] ?? '' }}" class="tax">
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

            $(document).on('change', '.vehicletypes', function() {
                let id = $(this).val();
                let booking_id = $('.booking_id').val();
                let $row = $(this).closest('tr');
                let $vehicleSelect = $row.find('select[name="vehicle[]"]');

                $vehicleSelect.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: '/get-vehicle-by-booking/' + id +'/booking/'+booking_id,
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
