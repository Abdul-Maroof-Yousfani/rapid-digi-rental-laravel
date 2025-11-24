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

    .form-group {
        margin-bottom: 0px !important;
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
            <form action="{{ url('customer-booking/'.$invoice->id) }}" method="post" enctype="multipart/form-data">
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
                                <h3>Edit Booking #{{ $invoice->zoho_invoice_number }}</h3>
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
                                        <option value="{{ $customer->id }}" {{ $invoice->booking->customer_id==$customer->id ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>Deposit Type</label>
                                    <select name="deposit_type" class="form-control select2" id="deposit_type">
                                        <option value="">Select Deposit Type</option>
                                        <option value="1" {{ $invoice->booking->deposit_type == 1 ? 'selected' : '' }}>CARDOO</option>
                                        <option value="2" {{ $invoice->booking->deposit_type == 2 ? 'selected' : '' }}>DEPOSIT LPO</option>
                                    </select>
                                </div>

                                <div class="form-group" id="deposit_amount_group">
                                    <label>Deposit Amount <span class="text-danger">*</span></label>
                                    <input type="number"
                                        value="{{ $invoice->booking->deposit->initial_deposit ?? 0 }}"
                                        name="deposit_amount"
                                        class="form-control">
                                    <input type="hidden" value="{{ $invoice->booking->id }}" name="booking_id" class="form-control disableClick booking_id">

                                </div>

                                <div class="form-group">
                                    <label>Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" value="{{ optional($invoice->booking)->started_at ? \Carbon\Carbon::parse($invoice->booking->started_at)->format('Y-m-d') : '' }}" name="started_at" class="form-control started_at" required>
                                </div>
                                
                                <div class="form-group" id="non_refundable_amount_group">
                                    <label>Non Refundable Amount <span class="text-danger">*</span></label>
                                    <input type="number"
                                        value="{{ $invoice->booking->non_refundable_amount ?? 0 }}"
                                        name="non_refundable_amount"
                                        class="form-control">
                                </div>
                                {{-- @if ($invoice->invoice_status=='sent')
                                <div class="form-group">
                                    <label>Reason For Update. <span class="text-danger">*</span></label>
                                    <input type="text" value="" name="reason" class="form-control" required>
                                </div>
                                @endif --}}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Sale Person <span class="text-danger">*</span></label>
                                    <select name="sale_person_id" class="form-control select2">
                                        <option value="">Select Sale Person</option>
                                        @foreach ($salePerson as $item)
                                        <option value="{{ $item->id }}" {{ $item->id==$invoice->booking->sale_person_id ? 'Selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Agreement No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ $invoice->booking->agreement_no }}" name="agreement_no" class="form-control agreement_no">
                                </div>

                                 <div class="form-group">
                                    <label>Terms</label>
                                    <select name="terms" id="terms" class="form-control select2">
                                        <option value="">Select Terms</option>

                                        <option value="Due on Receipt" {{ $invoice->booking->terms == 'Due on Receipt' ? 'selected' : '' }}>Due on Receipt</option>
                                        <option value="Adjustment Amount" {{ $invoice->booking->terms == 'Adjustment Amount' ? 'selected' : '' }}>Adjustment Amount</option>
                                        <option value="Insurance Payment" {{ $invoice->booking->terms == 'Insurance Payment' ? 'selected' : '' }}>Insurance Payment</option>
                                        <option value="Net 15" {{ $invoice->booking->terms == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                        <option value="Net 30" {{ $invoice->booking->terms == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                        <option value="Net 45" {{ $invoice->booking->terms == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                        <option value="Net 60" {{ $invoice->booking->terms == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                        <option value="Due end of the month" {{ $invoice->booking->terms == 'Due end of the month' ? 'selected' : '' }}>Due end of the month</option>
                                        <option value="Due end of next month" {{ $invoice->booking->terms == 'Due end of next month' ? 'selected' : '' }}>Due end of next month</option>
                                        <option value="Custom" {{ $invoice->booking->terms == 'Custom' ? 'selected' : '' }}>Custom</option>
                                    </select>

                                    @error('terms')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                                 <div class="form-group">
                                    <label>Due Date <span class="text-danger">*</span></label>
                                   <input
                                        type="date"
                                        name="due_date"
                                        class="form-control due_date"
                                        required
                                        value="{{ optional($invoice->booking)->due_date ? \Carbon\Carbon::parse($invoice->booking->due_date)->format('Y-m-d') : '' }}"
                                    >

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" style="display: none;">
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
                                                        id="addRowss">+</button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicleTableBody">
                                           
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-12 col-lg-12">
                    <div class="header-card head-flex">
                        <h4>Booking Details</h4>
                        <div class="card-header-form">
                           
                            <div class="input-group d-flex justify-content-end">
                                <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#ReplaceVehicleModal">
                                    Replace Vehicle
                                </button>

                                <button type="button" class="btn btn-success btn-md" id="addRow">+</button>
                            </div>
                        </div>
                       
                    </div>
                    <hr style=" border-bottom:1px solid #6c757d;">
                    <div class="lineItemBody" id="lineItemBody">
                        @foreach ($booking_data as $index => $item)
                        @php
                        $item->selectedTypeId = optional($item->vehicle)->vehicletypes ?? null;
                        $item->selectedVehicleId = $item->vehicle_id;
                        // $selectedVehicleId = $item->vehicle_id;
                        $selectedInTyId = $item->invoice_type->id ?? null;
                        // $selectedTypeId = $vehicleTypeMap[$selectedVehicleId] ?? null;
                        $filteredVehicles = $vehiclesByType[$item->selectedTypeId] ?? collect();
                        $vehicleObj = $vehicles->where('id', $item->selectedVehicleId)->first();
                        @endphp
                        <div class="card">
                            <div class="card-body lineItem">
                                <div class="close-btn">
                                    <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                                </div>

                                <div class="row">

                                    <div class="col-3" style="display: none;">
                                        <div class="form-group">
                                            <select name="invoiceTypes[]" class="form-control select2 invoiceTypes" required>
                                                <option value="null">Select Type</option>

                                            </select>

                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Start Date <span class="text-danger">*</span></label><br>
                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD" data-default="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Return Date <span class="text-danger">*</span></label><br>
                                            <input type="date" value="{{ \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') }}" name="return_date[]"
                                                class="form-control datemask return-date" placeholder="YYYY/MM/DD"
                                                data-default="{{ \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                            <select name="vehicletypes[]" class="form-control select2 vehicletypes">
                                                <option value="">Select Vehicle type</option>
                                                @foreach ($vehicletypes as $vtype)
                                                    <option value="{{ $vtype->id }}" 
                                                        {{ $item->selectedTypeId == $vtype->id ? 'selected' : '' }}>
                                                        {{ $vtype->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>
                                    @php
                                    $selectedVehicleText = optional($item->vehicle)->vehicle_name
                                    ?? (optional($item->vehicle)->number_plate . ' | ' . optional($item->vehicle)->temp_vehicle_detail)
                                    ?? 'N/A';
                                    @endphp

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Vehicle Name <span class="text-danger">*</span></label><br>
                                            <select name="vehicle[]" class="form-control select2 vehicle">
                                                <option value="">Select Vehicle</option>
                                                @foreach ($filteredVehicles as $vehicle)
                                                    @php $disable = $vehicle->status == 0 ? 'disabled' : ''; @endphp
                                                    <option value="{{ $vehicle->id }}" {{ $disable }} 
                                                        {{ $item->selectedVehicleId == $vehicle->id ? 'selected' : '' }}>
                                                        {{ $vehicle->number_plate . ' | ' . $vehicle->temp_vehicle_detail }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Tax (%)</label><br>
                                            <input type="hidden" name="tax_percent[]" value="$taxes['tax_percentage']" class="tax">
                                            <input type="hidden" name="tax_name[]" value="$taxes['tax_name']" class="tax">

                                            <select name="tax[]"
                                                class="form-control select2 zohotax" required>
                                                <option value="">Select Tax</option>
                                                @foreach ($taxlist['taxes'] as $taxes)
                                                @php
                                                $currentTaxId = data_get($zohocolumn, "invoice.line_items.$index.tax_id");
                                                @endphp
                                                <option value="{{ $taxes['tax_id'] }}"
                                                    data-percentage="{{ $taxes['tax_percentage'] }}"
                                                    {{ $currentTaxId == $taxes['tax_id'] ? 'selected' : '' }}>
                                                    {{ $taxes['tax_name'] }} ({{ $taxes['tax_percentage'].'%' }})
                                                </option>

                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                            <input type="text" value="{{ $item->price }}" name="price[]" class="form-control price">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="">Total Amount</label><br>
                                            <input type="number" value="" name="amount[]" class="form-control amount" readonly>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label for="">Description</label><br>
                                    <textarea name="description[]" style="width:100%;height: 100px !important;" class="form-control" id="" cols="60" rows="4" placeholder="Description">
                                    {{ $zohocolumn['invoice']['line_items'][$index]['description'] ?? '' }}
                                    </textarea>
                                </div>



                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="card-header-form">
                        <div class="input-group d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="addCharges">Add Charges</button>
                        </div>
                    </div>
                    <br>
                </div>


                <div class="col-12">
                    @if ($booking_data_charges->isNotEmpty())
                    @foreach ($booking_data_charges as $index => $item)
                    @php
                    $selectedInTyId = $item->deductiontype_id ?? $item->invoice_type->id ?? null;
                    $selectedTypeId = optional($item->vehicle)->vehicletypes ?? null;
                    $selectedVehicleId = $item->vehicle_id;
                    @endphp

                    <div class="card">
                        <div class="card-body lineItem">
                            <div class="close-btn">
                                <button type="button" style="font-size: 35px" class="btn btn-danger btn-md removeChargesRow">X</button>
                            </div>
                            <div class="row">

                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Type <span class="text-danger">*</span></label><br>
                                        <select name="invoiceTypes[]" class="form-control select2 invoiceTypes" required>
                                            <option value="">Select Type</option>
                                            @foreach ($invoiceTypes as $itype)
                                            <option value="{{ $itype->id }}" {{ $selectedInTyId == $itype->id ? 'selected' : '' }}>
                                                {{ $itype->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                <div class="col-3" style="display: none;">
                                    <div class="form-group">
                                        <label for="">Start Date <span class="text-danger">*</span></label><br>
                                        <input type="date" value="null" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                                    </div>
                                </div>
                                <div class="col-3" style="display: none;">
                                    <div class="form-group">
                                        <label for="">Return Date <span class="text-danger">*</span></label><br>
                                        <input type="date" value="null" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                        <select name="vehicletypes[]"
                                            class="form-control select2 vehicletypes2">
                                            <option value="0">Select Vehicle type</option>
                                            @foreach ($vehicleTypeMap as $vtype)
                                                <option value="{{ $vtype->id }}" {{ $selectedTypeId == $vtype->id ? 'selected' : '' }}>{{ $vtype->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Vehicle Name<span class="text-danger">*</span></label>
                                        <select name="vehicle[]" class="form-control select2 vehicle2">
                                            <option value="">Select Vehicle</option>
                                            @foreach($vehiclesByType as $type => $vehicles)
                                                @foreach($vehicles as $vehi)
                                                    <option value="{{ $vehi->id }}" {{ $selectedVehicleId == $vehi->id ? 'selected' : '' }}>
                                                        {{ $vehi->number_plate . ' | ' . $vehi->vehicle_name }}
                                                    </option>
                                                @endforeach
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                <div class="col-3" style="display: none;">
                                    <div class="form-group">
                                        <label for="">Tax (%)</label><br>
                                        <select name="tax[]" class="form-control select2 zohotax" readonly>
                                            <option value="null">Select Tax</option>

                                        </select>
                                    </div>
                                </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Quantity</label><br>
                                    <input type="number" name="quantity[]" value="1" class="form-control quantity">
                                </div>
                            </div>

                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                        <input type="text" value="{{ $item->item_total }}" name="price[]" class="form-control price" required>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Total Amount</label><br>
                                        <input type="number" value="" name="amount[]" class="form-control amount">
                                    </div>
                                </div>

                                <textarea name="description[]" style="width:100%;height: 100px !important; display: none;" class="form-control" id="" cols="60" rows="4" placeholder="Description"></textarea>

                            </div>

                        </div>
                    </div>
                    @endforeach
                    @endif
                    <div class="lineItemChargersBody" id="lineItemChargersBody">
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-6">
                    <div class="form-group">
                        <label>Invoice Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" cols="30" class="form-control" rows="10" required>{{ $invoice->booking->notes }}</textarea>
                    </div>
                </div>

                <input type="hidden" name="reason_of_update" id="hidden_reason">

        </div>
        <br>
        {{-- <div class="row">
            <div class="col-12 col-md-6 col-lg-6">
                <input hide type="submit" value="Update Booking" id="submitBtn"
                    class="btn btn-primary">
            </div>
        </div> --}}
         <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#UpdateReasonModal">
                                    Update-Booking
                                </button>
        </form>
</div>
</section>
</div>
@php
                    $selectedVehicleStatusId = App\Models\Vehicle::where('id', $invoice->booking->vehicle_id)->value('vehicle_status_id');
@endphp
<div class="modal fade" id="ReplaceVehicleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Replace Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group mt-2">
                    <label for="curr_vehicle">Current Vehicle</label>
                    <select id="curr_vehicle" class="form-control select2" disabled>
                        <option value="">Select Vehicle</option>
                        @foreach($vehiclesByType as $type => $vehicles)
                            @foreach($vehicles as $vehi)
                                <option value="{{ $vehi->id }}" {{ $invoice->booking->vehicle_id == $vehi->id ? 'selected' : '' }}>
                                    {{ $vehi->number_plate . ' | ' . $vehi->vehicle_name}}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="curr_vehicle_status">Current Vehicle Status</label>
                    <select id="curr_vehicle_status" class="form-control select2">
                        <option value="">Select Status</option>
                        @foreach($vehiclesStatuses as $vehiclesStatus)
                            <option value="{{ $vehiclesStatus->name }}" {{ $selectedVehicleStatusId == $vehiclesStatus->id ? 'selected' : '' }}>
                                {{ $vehiclesStatus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="new_vehicle">Assign New Vehicle</label>
                    <select id="new_vehicle" class="form-control select2" disabled>
                        <option value="">Select Vehicle</option>
                        @foreach($vehiclesByType as $type => $vehicles)
                            @foreach($vehicles as $vehi)
                                <option value="{{ $vehi->id }}" {{ $invoice->booking->replacement_vehicle_id == $vehi->id ? 'selected' : '' }}>
                                    {{ $vehi->number_plate . ' | ' . $vehi->vehicle_name}}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="reason_of_replacement">
                        Reason of Replacement <span style="color: red;">*</span>
                    </label>
                    <textarea id="reason_of_replacement" 
                            class="form-control" 
                            required 
                            rows="3"
                            placeholder="Enter reason here..."></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnReplaceVehicle">Update</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="UpdateReasonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reason of Updation for Zoho</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

           

                <div class="form-group mt-2">
                    <label for="reason_of_update">
                        Reason <span style="color: red;">*</span>
                    </label>
                    <textarea id="reason_of_update" 
                            class="form-control" 
                            required 
                            rows="3"
                            placeholder="Enter reason here..."></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="modalSubmitBtn">Update</button>

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
    $(document).ready(function() {
        function toggleDepositFields() {
            let depositType = $('#deposit_type').val();
            if (depositType === '') {
                $('#deposit_amount_group').show();
                $('#non_refundable_amount_group').hide();
            } else {
                $('#deposit_amount_group').hide();
                $('#non_refundable_amount_group').show();
            }
        }

        // Call on load and on change
        toggleDepositFields();
        $('#deposit_type').change(toggleDepositFields);



    });

    $('#modalSubmitBtn').click(function () {
        let reason = $('#reason_of_update').val().trim();

        if (reason === '') {
            alert('Reason is required!');
            return;
        }

        // Put reason inside hidden input
        $('#hidden_reason').val(reason);

        // Submit the main form
        $('form[action*="customer-booking"]').submit();
    });
</script>
<script>
    // Line items vehicles booking end date equals to Started at
    function applyMinDateToAllDateFields(startedAt) {
        if (startedAt) {
            $('.booking-date').each(function() {
                $(this).attr('min', startedAt);

                // Force reset if current value is less than new min
                if ($(this).val() < startedAt) {
                    $(this).val('');
                }
            });
            $('.return-date').each(function() {
                $(this).attr('min', startedAt);

                if ($(this).val() < startedAt) {
                    $(this).val('');
                }
            });
        }
    }

    // Multiple line item not selected same vehicle
    function updateVehicleOptions() {
        // Step 1: Collect all selected vehicle IDs
        let selectedVehicleIds = [];
        $('select[name="vehicle[]"]').each(function() {
            let val = $(this).val();
            if (val) selectedVehicleIds.push(val);
        });

        // Step 2: Loop over each vehicle dropdown
        $('select[name="vehicle[]"]').each(function() {
            let currentSelect = $(this);
            let currentVal = currentSelect.val();

            // Re-enable all options first
            currentSelect.find('option').prop('disabled', false);

            // Step 3: Disable options that are selected in other rows
            selectedVehicleIds.forEach(function(val) {
                if (val !== currentVal) {
                    currentSelect.find(`option[value="${val}"]`).prop('disabled', true);
                }
            });
        });
    }

    $(document).ready(function() {

        $(document).on('change', '#curr_vehicle_status', function() {
            let selectedStatus = $(this).val().toLowerCase();

            if (selectedStatus === 'garage') {
                $('#new_vehicle').prop('disabled', false);
            } else {
                $('#new_vehicle').prop('disabled', true).val('').trigger('change');
            }
        });

        $(document).on('keypress', '.agreement_no, .price', function(e) {
            if (e.key === '-' || e.which === 45) {
                e.preventDefault();
            }
        });

        let startedAt = $('.started_at').val();
        applyMinDateToAllDateFields(startedAt);

        $('.started_at').trigger('change');

        $('#addRow').click(function() {
            let newRow = `
                <div class="card">
                    <div class="card-body lineItem">
                        <div class="close-btn">
                            <button type="button" class="btn btn-danger btn-md removeRow">X</button>
                        </div>
                        
                        <div class="row">
                        
                            <div class="col-3" style="display: none;">
                                <div class="form-group">
                                    <select name="invoiceTypes[]" class="form-control select2 invoiceTypes" required>
                                        <option value="null">Select Type</option>

                                    </select>

                                </div>
                            </div>

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
                                    <input type="hidden" name="tax_percent[]" value="" class="tax">
                                                <input type="hidden" name="tax_name[]" value="" class="tax">
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
                                    <input type="text" value="" name="price[]" class="form-control price"  required>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Total Amount</label><br>
                                    <input type="number" value="" name="amount[]" class="form-control amount" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Description</label><br>
                            <textarea name="description[]" style="width:100%;height: 100px !important;"  class="form-control" id="" cols="60" rows="4" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>`;

            $('#lineItemBody').append(newRow);
            $('.select2').select2({
                width: '100%'
            });

            let startedAt = $('.started_at').val();
            applyMinDateToAllDateFields(startedAt);

        });

        $(document).on('click', '.removeRow', function() {
            if ($('.lineItemBody .card').length > 1) {
                $(this).closest('.card').remove();
            } else {
                alert("At least one booking box must remain.");
            }
        });

        $('#addCharges').click(function() {
            let newChargesRow = `
                <div class="card">
                    <div class="card-body lineItem">
                        <div class="close-btn">
                            <button type="button" style="font-size: 35px" class="btn btn-danger btn-md removeChargesRow">X</button>
                        </div>
                        <div class="row">

                        
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Type <span class="text-danger">*</span></label><br>
                                    <select name="invoiceTypes[]"
                                        class="form-control select2 invoiceTypes" required>
                                        <option value="">Select Type</option>
                                        @foreach ($invoiceTypes as $itype)
                                            <option value="{{ $itype->id }}">{{ $itype->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                             <div class="col-3" style="display: none;">
                                <div class="form-group">
                                    <label for="">Start Date <span class="text-danger">*</span></label><br>
                                    <input type="date" value="null" name="booking_date[]" class="form-control datemask booking-date" placeholder="YYYY/MM/DD">
                                </div>
                            </div>
                            <div class="col-3" style="display: none;">
                                <div class="form-group">
                                    <label for="">Return Date <span class="text-danger">*</span></label><br>
                                    <input type="date" value="null" name="return_date[]" class="form-control datemask return-date" placeholder="YYYY/MM/DD">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Vehicle Type <span class="text-danger">*</span></label><br>
                                    <select name="vehicletypes[]"
                                        class="form-control select2 vehicletypes2">
                                        <option value="0">Select Vehicle type</option>
                                        @foreach ($vehicleTypeMap as $vtype)
                                            <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Vehicle Name<span class="text-danger">*</span></label>
                                    <select name="vehicle[]" class="form-control select2 vehicle2">
                                        <option value="null">Select Vehicle</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-3" style="display: none;">
                                <div class="form-group">
                                    <label for="">Tax (%)</label><br>
                                    <select name="tax[]"class="form-control select2 zohotax" readonly>
                                        <option value="null">Select Tax</option>
                                       
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Quantity</label><br>
                                    <input type="number" name="quantity[]" value="1" class="form-control quantity">
                                </div>
                            </div>

                          <div class="col-3">
                            <div class="form-group">
                                <label for="">Price (AED) <span class="text-danger">*</span></label><br>
                                <input type="text" name="price[]" class="form-control price" step="0.01" min="0" value="" required>
                            </div>
                        </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Total Amount</label><br>
                                    <input type="number" value="" name="amount[]" class="form-control amount">
                                </div>
                            </div>

                         <textarea name="description[]" style="width:100%;height: 100px !important; display: none;"  class="form-control" id="" cols="60" rows="4" placeholder="Description"></textarea>

                        </div>
                       
                    </div>
                </div>`;

            $('#lineItemChargersBody').append(newChargesRow);
            $('.select2').select2({
                width: '100%'
            });

            let startedAt = $('.started_at').val();
            applyMinDateToAllDateFields(startedAt);

        });

        $(document).on('click', '.removeChargesRow', function() {
            $(this).closest('.card').remove();
        });



        $('#lineItemBody').on('change', '.booking-date, .return-date', function() {
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

        $(document).on('change', '.vehicletypes2', function() {
            let id = $(this).val();
            let $row = $(this).closest('.lineItem');
            let bookingId = $('.booking_id').val();
            let $vehicleSelect = $row.find('.vehicle2');

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


        $(document).on('change', '.vehicletypes', function() { 
    let $row = $(this).closest('.lineItem');
    let typeId = $(this).val();
    let $vehicleSelect = $row.find('select[name="vehicle[]"]');

    let bookingDate = $row.find('.booking-date').val();
    let returnDate = $row.find('.return-date').val();
    let bookingId = $('.booking_id').val();


    if (!bookingDate || !returnDate) {
        alert("Please select booking and return date first");
        return;
    }

    // Store selected vehicle before removing options
    let previousSelectedVehicle = $vehicleSelect.val();

    $vehicleSelect.empty().append('<option value="">Loading...</option>');

    $.ajax({
        url: `/get-vehicle-by-Type/${typeId}`,
        type: 'GET',
        data: {
            start_date: bookingDate,
            end_date: returnDate,
            bookingID: bookingId
        },
        success: function(response) {

            $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');

            $.each(response, function(key, vehicle) {
                $vehicleSelect.append(
                    `<option value="${vehicle.id}">
                        ${vehicle.number_plate} | ${vehicle.temp_vehicle_detail ?? vehicle.vehicle_name}
                    </option>`
                );
            });

            // Re-select if vehicle still exists
            if (previousSelectedVehicle && 
                $vehicleSelect.find(`option[value="${previousSelectedVehicle}"]`).length > 0) 
            {
                $vehicleSelect.val(previousSelectedVehicle).trigger('change');
            }

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


        $(document).on('click', '#btnReplaceVehicle', function () {
            var currentVehicle = $('#curr_vehicle').val();
            var newVehicle = $('#new_vehicle').val();
            var currVehicleStatus = $('#curr_vehicle_status').val();
            var reasonOfReplacement = $('#reason_of_replacement').val();
            let bookingId = $('.booking_id').val();


            $.ajax({
                url: '/replace-vehicle/' + bookingId,
                type: 'POST',
                data: {
                    current_vehicle_id: currentVehicle,
                    new_vehicle_id: newVehicle,
                    curr_vehicle_status: currVehicleStatus,
                    reason_of_replacement: reasonOfReplacement,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#ReplaceVehicleModal').modal('hide');
                    alert('Updated successfully!');
                 //   $('.vehicletypes2').val('').trigger('change');
                 $('#lineItemChargersBody .vehicletypes2 #reason_of_replacement').val('').trigger('change');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert('Something went wrong!');
                }
            });
        });


        function calculateAmount(row) {
            var taxVal = row.find('.zohotax').val();
            var zohotax = row.find('.zohotax option[value="' + taxVal + '"]').data('percentage') || 0;
            row.find('.tax').val(zohotax);

            var qty = parseFloat(row.find('.quantity').val()) || 1;
            var price = parseFloat(row.find('.price').val()) || 0;
            var grossPrice = price * qty;
            var taxAmount = (zohotax / 100) * grossPrice;
            var total = grossPrice + taxAmount;

            row.find('.amount').val(total.toFixed(2));
        }

        $(document).on('keyup', '.price', function() {
            let row = $(this).closest('.lineItem');
            calculateAmount(row);
        });

        $(document).on('change', '.zohotax', function() {
            let row = $(this).closest('.lineItem');
            calculateAmount(row);
        });

        $(document).on('keyup', '.quantity', function() {
            let row = $(this).closest('.lineItem');
            calculateAmount(row);
        });

        $('.zohotax').trigger('change');
        $('.price').trigger('change');
        $('.quantity').trigger('change');
    });
</script>
@endsection