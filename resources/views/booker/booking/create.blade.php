@extends('admin.master-main')
@section('content')

<style>
    .disableClick{ cursor: not-allowed !important; }
</style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="section-body">
                <form 
                {{-- action="{{ role_base_url('/booker') }}" method="post"  --}}
                id="booking-form"  enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Add Booker</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Customer  <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-control select2" required>
                                            <option value="">Select Customer</option>
                                            @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                    </div><br>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Notes <span class="text-danger">*</span></label>
                                        <textarea name="notes" cols="30" class="form-control" rows="10" required>{{ old('notes') }}</textarea>
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
                                <div class="table-responsive">
                                    <table class="table table-striped" id="vehicleTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    <div class="custom-checkbox custom-checkbox-table custom-control">
                                                        <input type="checkbox" data-checkboxes="mygroup" data-checkbox-role="dad" class="custom-control-input" id="checkbox-all">
                                                        <label for="checkbox-all" class="custom-control-label">&nbsp;</label>
                                                    </div>
                                                </th>
                                                <th>Vehicle Type <span class="text-danger">*</span></th>
                                                <th>Vehicle Name <span class="text-danger">*</span></th>
                                                <th>Investor</th>
                                                <th>No. Plate</th>
                                                <th>Booking Status</th>
                                                <th>Status</th>
                                                <th>Start Date <span class="text-danger">*</span></th>
                                                <th>Return Date <span class="text-danger">*</span></th>
                                                <th>Price <span class="text-danger">*</span></th>
                                                <th><button type="button" class="btn btn-success btn-md" id="addRow">+</button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicleTableBody">
                                            <tr>
                                                <td class="p-0 text-center">
                                                    <div class="custom-checkbox custom-control">
                                                        <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input">
                                                        <label class="custom-control-label">&nbsp;</label>
                                                    </div>
                                                </td>
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
                                                
                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="text" name="investor[]" disabled class="form-control investor disableClick">
                                                    </div>
                                                </td>

                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="text" name="number_plate[]" disabled class="form-control no_plate disableClick">
                                                    </div>
                                                </td>

                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="text" name="booking_status[]" disabled class="form-control booking_status disableClick">
                                                    </div>
                                                </td>

                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="text" name="status[]" disabled class="form-control status disableClick">
                                                    </div>
                                                </td>
                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="date" value="" name="booking_date[]" class="form-control datemask" placeholder="YYYY/MM/DD" required>
                                                    </div>
                                                </td>
                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="date" value="" name="return_date[]" class="form-control datemask" placeholder="YYYY/MM/DD" required>
                                                    </div>
                                                </td>
                                                <td class="align-middle"><br>
                                                    <div class="form-group">
                                                        <input type="text" value="" name="price[]" class="form-control" required>
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
                          </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Add Booker" id="submitBtn" name="submit" class="btn btn-primary">
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
<script>
    $(document).ready(function () {
        $('#addRow').click(function () {
            let newRow = `
                <tr>
                    <td class="p-0 text-center">
                        <div class="custom-checkbox custom-control">
                            <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input">
                            <label class="custom-control-label">&nbsp;</label>
                        </div>
                    </td>
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

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="investor[]" disabled class="form-control investor disableClick">
                        </div>
                    </td>
                    
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="number_plate[]" disabled class="form-control no_plate disableClick">
                        </div>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="number_plate[]" disabled class="form-control booking_status disableClick">
                        </div>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="number_plate[]" disabled class="form-control status disableClick">
                        </div>
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
                        <div class="form-group">
                            <input type="text" value="" name="price[]" class="form-control" required>
                        </div>
                    </td>

                    <th><button type="button" class="btn btn-danger btn-md removeRow">X</button></th>
                </tr>`;
            $('#vehicleTableBody').append(newRow);
            $('.select2').select2({ width: '100%' });
        });

        // Remove row when X button clicked
        $(document).on('click', '.removeRow', function () {
            $(this).closest('tr').remove();
            if($('#vehicleTableBody tr').length==0){
                let defaultRow = `
                <tr>
                    <td class="p-0 text-center">
                        <div class="custom-checkbox custom-control">
                            <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input">
                            <label class="custom-control-label">&nbsp;</label>
                        </div>
                    </td>
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

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="investor[]" disabled class="form-control investor disableClick">
                        </div>
                    </td>
                    
                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="number_plate[]" disabled class="form-control no_plate disableClick">
                        </div>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="booking_status[]" disabled class="form-control booking_status disableClick">
                        </div>
                    </td>

                    <td class="align-middle"><br>
                        <div class="form-group">
                            <input type="text" name="status[]" disabled class="form-control status disableClick">
                        </div>
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
                        <div class="form-group">
                            <input type="text" value="" name="price[]" class="form-control" required>
                        </div>
                    </td>

                    <th><button type="button" class="btn btn-danger btn-md removeRow">X</button></th>
                </tr>`;
                $("#vehicleTableBody").append(defaultRow);
                $('.select2').select2({ width: '100%' });
            }
        });

        $(document).on('change', '.vehicletypes', function () {
            let id = $(this).val();
            let $row = $(this).closest('tr');
            let $vehicleSelect = $row.find('select[name="vehicle[]"]');

            $vehicleSelect.empty().append('<option value="">Loading...</option>');

            $.ajax({
                url: '/get-vehicle-by-Type/' + id,
                type: 'GET',
                success: function (response) {
                    $vehicleSelect.empty().append('<option value="">Select Vehicle</option>');
                    $.each(response, function (key, vehicle) {
                        $vehicleSelect.append(
                            '<option value="' + vehicle.id + '">' +
                            (vehicle.temp_vehicle_detail ?? vehicle.vehicle_name) +
                            '</option>'
                        );
                    });
                }
            });
        });

        $(document).on('change', '.vehicle', function (){
            let id = $(this).val();
            let row = $(this).closest('tr');
            let no_plate= row.find('.no_plate');
            let investor= row.find('.investor');
            let booking_status= row.find('.booking_status');
            let status= row.find('.status');
            if(!id) {
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


        $("#booking-form").on('submit', function(e){
            e.preventDefault();
            let formData= new FormData(this);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '{{ role_base_route("customer-booking.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                        }).then(() => {
                            $('#booking-form')[0].reset();
                            window.location.href = '{{ role_base_route("customer-booking.index") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Zoho Invoice Error!',
                            text: response.message,
                        });
                    }
                }
            });
        });
    });
</script>

@endsection
