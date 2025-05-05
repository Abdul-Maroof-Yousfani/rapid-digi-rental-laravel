
@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
          <div class="section-body">
            {{-- <form action="{{ auth()->user()->hasRole('admin') ? url("admin/customer/".$customers->id) : url("booker/customer/".$customers->id) }}" method="post"> --}}
            <form action="{{ role_base_url("customer/".$customers->id) }}" method="post">
                @csrf
                @method('put')
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                          <div class="col-md-6">
                              <h3>Edit Customer</h3>
                          </div>
                      </div>
                  </div>
              </div>
                <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Customer name  <span class="text-danger">*</span></label>
                            <input type="text" value="{{ $customers->customer_name }}" name="customer_name" class="form-control">
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
                                <input type="email" value="{{ $customers->email }}" name="email" class="form-control email">
                            </div>
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                    </div>
                                </div>
                                <input type="number" value="{{ $customers->phone }}" name="phone" class="form-control">
                            </div>
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                          <label>Driving Licence  <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-address-card"></i>
                                </div>
                            </div>
                              <input type="number" value="{{ $customers->licence }}" name="licence" class="form-control licence">
                          </div>
                          @error('licence') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>C-NIC  <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                </div>
                                <input type="number" value="{{ $customers->cnic }}" name="cnic" class="form-control cnic">
                            </div>
                            @error('cnic') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                        <label>Date Of Birth  <span class="text-danger">*</span></label>
                            <input type="date" value="{{ $customers->dob }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                            @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                          <label>Gender  <span class="text-danger">*</span></label>
                          <select name="gender" class="form-control">
                              <option value="">Select Gender</option>
                              <option value="male" {{ $customers->gender=="male" ? "selected" : "" }}>Male</option>
                              <option value="female" {{ $customers->gender=="female" ? "selected" : "" }}>Female</option>
                          </select>
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
                        <div class="form-group">
                            <label>Postal Code </label>
                            <input type="text" value="{{ $customers->postal_code }}" name="postal_code" class="form-control postal_code">
                        </div>

                        <div class="form-group">
                          <label>Address  <span class="text-danger">*</span></label>
                          <textarea name="address" cols="30" class="form-control" rows="10">{{ $customers->address }}</textarea>
                          @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Status</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="1" class="selectgroup-input-radio" {{ $customers->status=="1" ? "checked" : "" }}>
                                    <span class="selectgroup-button">Active</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="0" class="selectgroup-input-radio" {{ $customers->status=="0" ? "checked" : "" }}>
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
                    <input type="submit" value="Edit Customer" name="submit" class="btn btn-primary">
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
                    <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                    <span class="selectgroup-button">Light</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                    <span class="selectgroup-button">Dark</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar" checked>
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



@endsection
