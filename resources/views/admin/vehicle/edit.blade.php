
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
          <div class="section-body">
            <form action="{{ url('admin/vehicle/'.$vehicle->id) }}" method="post">
                @csrf
                @method('put')
                <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                    <div class="card-header">
                        <h4>Edit Vehicle</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Vehicle name  <span class="text-danger">*</span></label>
                            <input type="text" value="{{ $vehicle->vehicle_name }}" name="vehicle_name" class="form-control">
                            @error('vehicle_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Car Make <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                    </div>
                                </div>
                                <input type="text" value="{{ $vehicle->car_make }}" name="car_make" class="form-control">
                            </div>
                            @error('car_make') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Year  <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                <div class="input-group-text">
                                    $
                                </div>
                                </div>
                                <input type="text" value="{{ $vehicle->year }}" name="year" class="form-control year">
                            </div>
                            @error('year') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                          <label>Number Plate  <span class="text-danger">*</span></label>
                          <div class="input-group">
                              <div class="input-group-prepend">
                              <div class="input-group-text">
                                  $
                              </div>
                              </div>
                              <input type="text" value="{{ $vehicle->number_plate }}" name="number_plate" class="form-control number_plate">
                          </div>
                          @error('number_plate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                    </div>
                    </div>

                </div>
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                    <div class="card-header">
                        <h4>Edit Vehicle</h4>
                    </div>
                    <div class="card-body">
                        

                        <div class="form-group">
                          <label>Vehicle Type  <span class="text-danger">*</span></label>
                          <select name="vehicletypes" class="form-control">
                              <option value="">Select Vehicle</option>
                              @foreach ($vehicletypes as $vtype)
                              <option value="{{ $vtype->id }}" {{ $vehicle->vehicletypes==$vtype->id ? "selected" : "" }}>{{ $vtype->name }}</option>
                              @endforeach
                          </select>
                          @error('vehicletypes') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                          <label>Investor  <span class="text-danger">*</span></label>
                          <select name="investor_id" class="form-control">
                              <option value="">Select Investor</option>
                              @foreach ($investor as $item)
                              <option value="{{ $item->id }}" {{ $vehicle->investor_id==$item->id ? "selected" : "" }}>{{ $item->name }}</option>
                              @endforeach
                          </select>
                          @error('investor_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vehicle Status</label>
                            <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status" value="1" class="selectgroup-input-radio" checked>
                                <span class="selectgroup-button">Active</span>
                            </label>

                            <label class="selectgroup-item">
                                <input type="radio" name="status" value="0" class="selectgroup-input-radio">
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
                    <input type="submit" value="Update Vehicle" name="submit" class="btn btn-primary">
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
