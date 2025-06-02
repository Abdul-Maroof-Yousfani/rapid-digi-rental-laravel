@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@php $userRole= Auth::user()->getRoleNames()->first(); @endphp
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Customer List</h3>
                            <span>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#customerModal">
                                    Add Customer
                                </button>
                                <a href="{{ auth()->user()->hasRole('admin') ? role_base_route('syncCustomersFromZoho') : role_base_route('syncCustomersFromZoho') }}" class="btn btn-primary {{ $shouldEnableSync ? '' : 'disabled pointer-events-none opacity-50' }}" >
                                    Sync From Zoho
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                    <div class="card-body">
                        <form class="filterForm">
                            <div class="row">
                                <div class="col-2">
                                    <label for="">From Date</label>
                                    <input type="date" class="form-control" id="fromDate">
                                </div>
                                <div class="col-2">
                                    <label for="">.</label>
                                    <input type="text" placeholder="Between" class="form-control" disabled>
                                </div>
                                <div class="col-2">
                                    <label for="">To Date</label>
                                    <input type="date" class="form-control" id="toDate">
                                </div>
                                <div class="col-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary" onclick="filterdata()">
                                        Filter Data
                                    </button>
                                </div>
                            </div><br>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="customerResponseList" /*id="tableExport"*/ style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>S.no</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>CNIC</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="customerList">
                                    @php $number=1; @endphp
                                    @foreach ($customers as $item)
                                    <tr data-id="{{ $item->id }}">
                                        <td>{{ $number }}.</td>
                                        <td>{{ $item->customer_name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->cnic }}</td>
                                        <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            {{-- <a href="@can('manage customers') {{ role_base_url("customer/".$item->id."/edit") }} @endcan" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a> --}}
                                            <button type="button" class="btn btn-warning btn-sm ajax-edit-btn" data-id="{{ $item->id }}" data-modal-id="editCustomerModal">
                                                <i class="far fa-edit"></i> Edit
                                            </button>
                                            <form action="{{ auth()->user()->hasRole('admin') ? url('admin/customer/'.$item->id) : url('booker/customer/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>
                                                    Delete
                                                </button>
                                            </form>

                                        </td>
                                    </tr>
                                    @php $number++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
          </div>
        </section>
      </div>

        <!-- Create Model Code -->
        <div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form class="ajax-form" data-url="{{ role_base_url('customer') }}" data-target-table="#customerResponseList" data-render-function="renderCustomerRow" data-modal-id="customerModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Customer</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Customer name <span class="text-danger">*</span></label>
                                                        <input type="text" value="{{ old('customer_name') }}" name="customer_name" class="form-control">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Email (Optional)</label>
                                                        <input type="email" value="{{ old('email') }}" name="email" class="form-control email">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Phone Number <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('phone') }}" name="phone" class="form-control">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Driving Licence <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('licence') }}" name="licence" class="form-control licence">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>C-NIC <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('cnic') }}" name="cnic" class="form-control cnic">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Date Of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" value="{{ old('dob') }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Gender <span class="text-danger">*</span></label>
                                                        <select name="gender" class="form-control">
                                                            <option value="">Select Gender</option>
                                                            <option value="male">Male</option>
                                                            <option value="female">Female</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>City </label>
                                                        <select name="city" class="form-control select2">
                                                            <option value="">Select City</option>
                                                            <option>Karachi</option>
                                                            <option>Hydrabad</option>
                                                            <option>Peshawar</option>
                                                            <option>Rawalpindi</option>
                                                            <option>Islamabad</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>State </label>
                                                        <select name="state" class="form-control select2">
                                                            <option value="">Select Sindh</option>
                                                            <option>Sindh</option>
                                                            <option>Punjab</option>
                                                            <option>KPK</option>
                                                            <option>Balochistan</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Country </label>
                                                        <select name="country" class="form-control select2">
                                                            <option value="">Select Country</option>
                                                            <option>Pakistan</option>
                                                            <option>China</option>
                                                            <option>Uzbakistan</option>
                                                            <option>UAE</option>
                                                            <option>Russia</option>
                                                            <option>America (USA)</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Postal Code </label>
                                                        <input type="text" name="postal_code" class="form-control postal_code">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Address <span class="text-danger">*</span></label>
                                                        <textarea name="address" cols="30" class="form-control" rows="10">{{ old('address') }}</textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <div class="form-group">
                                                        <label class="form-label">Customer Status</label>
                                                        <div class="selectgroup w-100">
                                                            <label class="selectgroup-item">
                                                                <input type="radio" name="status" value="1"
                                                                    class="selectgroup-input-radio" checked>
                                                                <span class="selectgroup-button">Active</span>
                                                            </label>

                                                            <label class="selectgroup-item">
                                                                <input type="radio" name="status" value="0"
                                                                    class="selectgroup-input-radio">
                                                                <span class="selectgroup-button">Inactive</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Edit Model Code -->
        <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="customerEditForm" method="POST" class="ajax-update-form"
                        data-url="{{ url($userRole.'/customer') }}/:id"
                        data-fetch-url="{{ url('get-customer-for-edit-form/:id') }}"
                        data-target-table="#customerResponseList"
                        data-render-function="renderCustomerRow"
                        data-modal-id="editCustomerModal"
                        data-callback="populateCustomerForm">
                <input type="hidden" name="_method" value="PUT">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Customer</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" style="width:100%;">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Customer name <span class="text-danger">*</span></label>
                                                        <input type="text" value="{{ old('customer_name') }}" name="customer_name" class="form-control">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Email (Optional)</label>
                                                        <input type="email" value="{{ old('email') }}" name="email" class="form-control email">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Phone Number <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('phone') }}" name="phone" class="form-control">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Driving Licence <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('licence') }}" name="licence" class="form-control licence">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>C-NIC <span class="text-danger">*</span></label>
                                                        <input type="number" value="{{ old('cnic') }}" name="cnic" class="form-control cnic">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Date Of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" value="{{ old('dob') }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Gender <span class="text-danger">*</span></label>
                                                        <select name="gender" class="form-control">
                                                            <option value="">Select Gender</option>
                                                            <option value="male">Male</option>
                                                            <option value="female">Female</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>City </label><br>
                                                        <select name="city" class="form-control select2">
                                                            <option value="">Select City</option>
                                                            <option>Karachi</option>
                                                            <option>Hydrabad</option>
                                                            <option>Peshawar</option>
                                                            <option>Rawalpindi</option>
                                                            <option>Islamabad</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>State </label><br>
                                                        <select name="state" class="form-control select2">
                                                            <option value="">Select Sindh</option>
                                                            <option>Sindh</option>
                                                            <option>Punjab</option>
                                                            <option>KPK</option>
                                                            <option>Balochistan</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Country </label><br>
                                                        <select name="country" class="form-control select2">
                                                            <option value="">Select Country</option>
                                                            <option>Pakistan</option>
                                                            <option>China</option>
                                                            <option>Uzbakistan</option>
                                                            <option>UAE</option>
                                                            <option>Russia</option>
                                                            <option>America (USA)</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Postal Code </label>
                                                        <input type="text" name="postal_code" class="form-control postal_code">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label>Address <span class="text-danger">*</span></label>
                                                        <textarea name="address" cols="30" class="form-control" rows="10">{{ old('address') }}</textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <div class="form-group">
                                                        <label class="form-label">Customer Status</label><br>
                                                        <div class="selectgroup w-100">
                                                            <label class="selectgroup-item">
                                                                <input type="radio" name="status" value="1"
                                                                    class="selectgroup-input-radio" checked>
                                                                <span class="selectgroup-button">Active</span>
                                                            </label>

                                                            <label class="selectgroup-item">
                                                                <input type="radio" name="status" value="0"
                                                                    class="selectgroup-input-radio">
                                                                <span class="selectgroup-button">Inactive</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
@endsection


@section('script')
    <script type="text/javascript">

        function filterdata(){
            let fromDate= $('#fromDate').val();
            let toDate= $('#toDate').val();
            let data= { fromDate : fromDate , toDate : toDate };
            if (!fromDate || !toDate) {
                alert('Please select both dates.');
                return;
            }
            $.ajax({
                url: '/getCustomerList',
                method: 'get',
                data: data,
                success:function(response){
                    $('#customerList').html(response);
                }
            });
        }

    </script>
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `{{ session('error') }}`.replace(/\n/g, '\n'),
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
