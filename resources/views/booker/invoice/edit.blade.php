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
                <form action="{{ url('booker/booking/'. $invoice->id .'/update-invoice') }}" method="post" enctype="multipart/form-data">
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
                                    <h3>Edit Invoice - {{ $invoice->zoho_invoice_number }}</h3>
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

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Invoice Notes <span class="text-danger">*</span></label>
                                        <textarea name="notes" cols="30" class="form-control" rows="10" required></textarea>
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
                                <div class="card-body p-0">
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
                                                    <th>Type <span class="text-danger">*</span></th>
                                                    <th>Quantity <span class="text-danger">*</span></th>
                                                    <th>Discount <span class="text-danger"></span></th>
                                                    <th>Tax (%) <span class="text-danger"></span></th>
                                                    <th>Price (AED) <span class="text-danger">*</span></th>
                                                    <th>Amount (AED)<span class="text-danger">*</span></th>
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
                                                            <textarea name="description[]" class="form-control" id="" cols="60" rows="3">

                                                            </textarea>
                                                        </div>
                                                    </td>

                                                    <td class="align-middle investor"><br>
                                                        {{ $vehicleObj->investor->name ?? 'N/A' }}
                                                    </td>

                                                    <td class="align-middle no_plate"><br>
                                                        {{ $vehicleObj->number_plate ?? 'N/A' }}
                                                    </td>

                                                    <td class="align-middle booking_status"><br>
                                                        {{ $vehicleObj->booking_status === null ? 'N/A' : ($vehicleObj->booking_status==1 ? 'Available' : 'Not Available') }}
                                                    </td>

                                                    <td class="align-middle status"><br>
                                                        {{ $vehicleObj->status === null ? 'N/A' :  ($vehicleObj->status==1 ? 'Active' : 'Inactive') }}
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}" name="booking_date[]"
                                                                class="form-control datemask" placeholder="YYYY/MM/DD"
                                                                >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') }}" name="return_date[]"
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
                                                        <div class="form-group">
                                                            @php
                                                                $discount = $zohocolumn['invoice']['line_items'][$index]['discount'] ?? '';
                                                                $discount = str_replace('%', '', $discount);
                                                            @endphp
                                                            <input type="number" value="{{ floatval($discount) }}" name="discount[]" class="form-control discount" >
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><br>
                                                        <div class="form-group">
                                                            <input type="number" value="{{ $zohocolumn['invoice']['line_items'][$index]['tax_percentage'] ?? '' }}" name="tax[]" class="form-control tax" >
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
        <div class="settingSidebar">
            <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i>
            </a>
            <div class="settingSidebar-body ps-container ps-theme-default">
                <div class=" fade show active">
                    <div class="setting-panel-header">Setting Panel
                    </div>
                    <div class="p-15 border-bottom">
                        <h6 class="font-medium m-b-10">Select Layout</h6>
                        <div class="selectgroup layout-color w-50">
                            <label class="selectgroup-item">
                                <input type="radio" name="value" value="1"
                                    class="selectgroup-input-radio select-layout" checked>
                                <span class="selectgroup-button">Light</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="value" value="2"
                                    class="selectgroup-input-radio select-layout">
                                <span class="selectgroup-button">Dark</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-15 border-bottom">
                        <h6 class="font-medium m-b-10">Sidebar Color</h6>
                        <div class="selectgroup selectgroup-pills sidebar-color">
                            <label class="selectgroup-item">
                                <input type="radio" name="icon-input" value="1"
                                    class="selectgroup-input select-sidebar">
                                <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                    data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="icon-input" value="2"
                                    class="selectgroup-input select-sidebar" checked>
                                <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                    data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                            </label>
                        </div>
                    </div>
                    <div class="p-15 border-bottom">
                        <h6 class="font-medium m-b-10">Color Theme</h6>
                        <div class="theme-setting-options">
                            <ul class="choose-theme list-unstyled mb-0">
                                <li title="white" class="active">
                                    <div class="white"></div>
                                </li>
                                <li title="cyan">
                                    <div class="cyan"></div>
                                </li>
                                <li title="black">
                                    <div class="black"></div>
                                </li>
                                <li title="purple">
                                    <div class="purple"></div>
                                </li>
                                <li title="orange">
                                    <div class="orange"></div>
                                </li>
                                <li title="green">
                                    <div class="green"></div>
                                </li>
                                <li title="red">
                                    <div class="red"></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="p-15 border-bottom">
                        <div class="theme-setting-options">
                            <label class="m-b-0">
                                <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                    id="mini_sidebar_setting">
                                <span class="custom-switch-indicator"></span>
                                <span class="control-label p-l-10">Mini Sidebar</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-15 border-bottom">
                        <div class="theme-setting-options">
                            <label class="m-b-0">
                                <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                    id="sticky_header_setting">
                                <span class="custom-switch-indicator"></span>
                                <span class="control-label p-l-10">Sticky Header</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                        <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                            <i class="fas fa-undo"></i> Restore Default
                        </a>
                    </div>
                </div>
            </div>
        </div>
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

                    <td class="align-middle investor"><br></td>

                    <td class="align-middle no_plate"><br></td>

                    <td class="align-middle booking_status"><br></td>

                    <td class="align-middle status"><br></td>

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
                        <div class="form-group">
                            <input type="number" value="" name="discount[]" class="form-control discount" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="tax[]" class="form-control tax" >
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
                        <div class="form-group">
                            <input type="number" value="" name="discount[]" class="form-control discount" >
                        </div>
                    </td>
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="number" value="" name="tax[]" class="form-control tax" >
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

            $(document).on('change', '.price, .quantity, .discount, .tax', function(){
                var row = $(this).closest('tr');
                var price = parseFloat(row.find('.price').val()) || null;
                var qty = parseFloat(row.find('.quantity').val()) || null;
                var discount = parseFloat(row.find('.discount').val()) || null;
                var tax = parseFloat(row.find('.tax').val()) || null;
                var subtotal = price*qty;

                var discountAmount= (discount/100) * subtotal;
                var taxAmount= (tax/100) * subtotal;
                var lessDiscount= subtotal - discountAmount;
                var total= lessDiscount + taxAmount;
                row.find('.amount').val(total);
            });

        });
    </script>
@endsection
