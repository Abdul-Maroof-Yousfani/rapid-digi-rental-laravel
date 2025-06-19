@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @can('manage customers')
                {{-- <form action="{{ auth()->user()->hasRole('admin') ? route('admin.customer.store') : route('booker.customer.store') }}" method="post"> --}}
                <form action="{{ role_base_url('customer') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Add Customer</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Customer name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                            <input type="text" value="{{ old('customer_name') }}" name="customer_name" class="form-control">
                                        </div>
                                        @error('customer_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Email (Optional)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </div>
                                            </div>
                                            <input type="email" value="{{ old('email') }}" name="email"
                                                class="form-control email">
                                        </div>
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-phone"></i>
                                                </div>
                                            </div>
                                            {{-- <input type="number" value="{{ old('phone') }}" name="phone" class="form-control"> --}}
                                            <input type="tel" id="uaePhone" name="phone" class="form-control" placeholder="+971-xx-xxxxxxx" maxlength="17">
                                        </div>
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Driving Licence <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-address-card"></i>
                                                </div>
                                            </div>
                                            <input type="number" value="{{ old('licence') }}" name="licence"
                                                class="form-control licence">
                                        </div>
                                        @error('licence')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>C-NIC <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-id-card"></i>
                                                </div>
                                            </div>
                                            <input type="number" value="{{ old('cnic') }}" name="cnic"
                                                class="form-control cnic">
                                        </div>
                                        @error('cnic')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Date Of Birth <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-birthday-cake"></i>
                                                </div>
                                            </div>
                                            <input type="date" value="{{ old('dob') }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                                        </div>
                                        @error('dob')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>


                                    <div class="form-group">
                                        <label>Gender <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-venus-mars"></i>
                                                </div>
                                            </div>
                                            <select name="gender" class="form-control">
                                                <option value="">Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
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
                                    <span></span>
                                    <div class="form-group">
                                        <label>Postal Code </label>
                                        <input type="text" name="postal_code" class="form-control postal_code">
                                    </div>


                                    <div class="form-group">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="address" cols="30" class="form-control" rows="10">{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

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
                                    </div><br>

                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Add Customer" name="submit" class="btn btn-primary">
                        </div>
                    </div>
                </form>
                @endcan
            </div>
        </section>
    </div>
@endsection

@section('script')

<script src="{{ asset('assets/js/forms-format.js') }}"></script>

@endsection
