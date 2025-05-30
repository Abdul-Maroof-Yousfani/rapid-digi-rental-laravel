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
                      <h3 class="mb-0">Status List</h3>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vehicleStatusModal">
                            Add Status
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
                      <table class="table table-striped table-hover" id="vehicleStatusResponseList" style="width:100%;">
                        <thead>
                          <tr>
                            <th>S no.</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $number=1; @endphp
                          @foreach ($status as $item)
                          <tr data-id="{{ $item->id }}">
                              <td>{{ $number }}.</td>
                              <td>{{ $item->name }}</td>
                              <td>
                                  {{-- <a href='{{ url("admin/vehicle-status/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a> --}}
                                   <button type="button" class="btn btn-warning btn-sm ajax-edit-btn" data-id="{{ $item->id }}" data-modal-id="editVehicleStatusModal">
                                        <i class="far fa-edit"></i> Edit
                                   </button>
                                  <form action="{{ url('admin/vehicle-status/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
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


    <!-- Create Model Code -->
    <div class="modal fade" id="vehicleStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form class="ajax-form" data-url="{{ url('admin/vehicle-status') }}" data-target-table="#vehicleStatusResponseList" data-render-function="renderVehicleStatusRow" data-modal-id="vehicleStatusModal">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Status</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-toggle-on"></i>
                                    </div>
                                </div>
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control name" required>
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
    <div class="modal fade" id="editVehicleStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="vehicleStatusEditForm" method="POST" class="ajax-update-form"
                        data-url="{{ url('admin/vehicle-status') }}/:id"
                        data-fetch-url="{{ url('get-vehicle-status-edit-form/:id') }}"
                        data-target-table="#vehicleStatusResponseList"
                        data-render-function="renderVehicleStatusRow"
                        data-modal-id="editVehicleStatusModal"
                        data-callback="populateVehicleStatusForm">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Status</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-toggle-on"></i>
                                    </div>
                                </div>
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control name" required>
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
