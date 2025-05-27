@extends('admin.master-main')

@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <h3 class="mb-0">Vehicle List</h3>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vehicleModal">
                            Add Vehicle
                      </button>
                    </div>
                  </div>
                </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="vehicleResponseList" style="width:100%;">
                        <thead>
                          <tr>
                            <th>S no.</th>
                            <th>Vehicle Name</th>
                            <th>Vehicle type</th>
                            <th>Investor</th>
                            <th>Number Plate</th>
                            <th>Car Make</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $number=1; @endphp
                          @foreach ($vehicle as $item)
                          <tr>
                              <td>{{ $number }}.</td>
                              <td>{{ $item->vehicle_name ?? $item->temp_vehicle_detail }}</td>
                              <td>{{ $item->vehicletype->name }}</td>
                              <td>{{ $item->investor->name }}</td>
                              <td>{{ $item->number_plate }}</td>
                              <td>{{ $item->car_make }}</td>
                              <td>{{ $item->year }}</td>
                              <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
                              <td>
                                  <a href='{{ url("admin/vehicle/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                                  <form action="{{ url('admin/vehicle/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
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

    <div class="modal fade" id="vehicleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form class="ajax-form" data-url="{{ url('admin/vehicle') }}" data-target-table="#vehicleResponseList" data-render-function="renderVehicleRow" data-modal-id="vehicleModal">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Vehicle</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Vehicle name  <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">
                                                            <i class="fas fa-car-side"></i>
                                                            </div>
                                                        </div>
                                                        <input type="text" value="{{ old('vehicle_name') }}" name="vehicle_name" class="form-control">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Car Make <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">
                                                                <i class="fas fa-tools"></i>
                                                            </div>
                                                        </div>
                                                        <input type="text" value="{{ old('car_make') }}" name="car_make" class="form-control car_make">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
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
                                            </td>
                                            <td>
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
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Year  <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">
                                                                <i class="fas fa-calendar"></i>
                                                            </div>
                                                        </div>
                                                        <input type="number" value="{{ old('year') }}" name="year" class="form-control year">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Number Plate  <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <i class="fa-id-card"></i>
                                                        </div>
                                                        </div>
                                                        <input type="text" value="{{ old('number_plate') }}" name="number_plate" class="form-control number_plate">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
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
      document.addEventListener('DOMContentLoaded', function () {
          const deleteButtons = document.querySelectorAll('.delete-confirm');
          deleteButtons.forEach(button => {
              button.addEventListener('click', function (e) {
                  e.preventDefault(); // Stop form submit
                  const form = this.closest('form');
                  Swal.fire({
                      title: 'Are you sure?',
                      text: "You won't be able to revert this!",
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#d33',
                      cancelButtonColor: '#3085d6',
                      confirmButtonText: 'Yes, delete it!'
                  }).then((result) => {
                      if (result.isConfirmed) {
                          form.submit();
                      }
                  });
              });
          });
      });
  </script>

@endsection
