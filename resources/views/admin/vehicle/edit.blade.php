
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
      </div>



@endsection
