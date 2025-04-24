
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
          <div class="section-body">
            <form action="{{ url('admin/vehicle') }}" method="post">
                @csrf
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <h3>Add Vehicle</h3>
                          </div>
                          <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"> Import Csv </button>
                          </div>
                        </div>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12 col-md-6 col-lg-6">
                      <div class="card">
                      <div class="card-body">
                          <div class="form-group">
                              <label>Vehicle name  <span class="text-danger">*</span></label>
                              <input type="text" value="{{ old('vehicle_name') }}" name="vehicle_name" class="form-control">
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
                                  <input type="text" value="{{ old('car_make') }}" name="car_make" class="form-control">
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
                                  <input type="text" value="{{ old('year') }}" name="year" class="form-control year">
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
                                <input type="text" value="{{ old('number_plate') }}" name="number_plate" class="form-control number_plate">
                            </div>
                            @error('number_plate') <span class="text-danger">{{ $message }}</span> @enderror
                          </div>

                      </div>
                      </div>

                  </div>
                  <div class="col-12 col-md-6 col-lg-6">
                      <div class="card">
                      {{-- <div class="card-header">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"> Import Csv </button>
                      </div> --}}
                      <div class="card-body">
                        
                        
                        <div class="form-group">
                            <label>Vehicle Type  <span class="text-danger">*</span></label>
                            <select name="vehicletypes" class="form-control select2">
                                <option value="">Select Vehicle</option>
                                @foreach ($vehicletypes as $vtype)
                                <option value="{{ $vtype->id }}">{{ $vtype->name }}</option>
                                @endforeach
                            </select>
                            @error('vehicletypes') <span class="text-danger">{{ $message }}</span> @enderror
                          </div>

                          <div class="form-group">
                            <label>Investor  <span class="text-danger">*</span></label>
                            <select name="investor_id" class="form-control select2">
                                <option value="">Select Investor</option>
                                @foreach ($investor as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
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
                    <input type="submit" value="Add Vehicle" name="submit" class="btn btn-primary">
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


      <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
      
            <div class="modal-header">
              <h5 class="modal-title" id="modalLabel">Modal Title</h5>
              {{-- <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">&times;</button> --}}
            </div>
      
            <div class="modal-body">
              <form action="{{ url('admin/vehicle/import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="importCsv">Upload CSV File <span class="text-danger">*</span></label>
                    <input type="file" name="importCsv" accept=".csv" class="form-control" required>
                    <small class="form-text text-muted">Only <strong>.csv</strong> files are allowed.</small>
                    @if ($errors->has('importCsv'))
                        <div class="text-danger mt-1">
                            {{ $errors->first('importCsv') }}
                        </div>
                    @endif
                </div>
            
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
            
            </div>
      
          </div>
        </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection


@section('script')
@if ($errors->has('importCsv'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importModal = new bootstrap.Modal(document.getElementById('myModal'));
        importModal.show();
    });
</script>
@endif
@endsection