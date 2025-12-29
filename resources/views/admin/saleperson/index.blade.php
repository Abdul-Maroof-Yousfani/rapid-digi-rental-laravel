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
                        <h3 class="mb-0">Sale Person List</h3>
                            <span>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSaleManModal">
                                    Add Salesperson
                                </button>
                                 <a href="{{ route('syncSalespersonFromZoho') }}" class="btn btn-primary" >
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
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="saleMenResponseList" style="width:100%;">
                        <thead>
                          <tr>
                            <th>S no.</th>
                            <th>Sale Person</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $number=1; @endphp
                          @foreach ($salePerson as $item)
                          <tr data-id="{{ $item->id }}">
                              <td>{{ $number }}.</td>
                              <td>{{ $item->name }}</td>
                              <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
                              <td>
                                  {{-- <a href='{{ url("admin/sale-person/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a> --}}
                                    <button type="button" class="btn btn-warning btn-sm ajax-edit-btn" data-id="{{ $item->id }}" data-modal-id="EditsaleManModal">
                                        <i class="far fa-edit"></i> Edit
                                    </button>
                                  <form action="{{ url('sale-person/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
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

      <!-- Create Modal Form -->
        <div class="modal fade" id="createSaleManModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form class="ajax-form" data-url="{{ url('sale-person') }}" data-target-table="#saleMenResponseList" data-render-function="renderSaleManRow" data-modal-id="createSaleManModal">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Sale Person</h5>
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
                                                        <label>Sale Person Name <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            </div>
                                                            <input type="text" value="{{ old('name') }}" name="name" class="form-control name">
                                                        </div>
                                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="form-label">Sale Person Status</label>
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

        <!-- Edit Modal Form -->
        <div class="modal fade" id="EditsaleManModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="saleManEditForm" method="POST" class="ajax-update-form"
                        data-url="{{ url('sale-person') }}/:id"
                        data-fetch-url="{{ url('get-salemen-for-edit-form/:id') }}"
                        data-target-table="#saleMenResponseList"
                        data-render-function="renderSaleManRow"
                        data-modal-id="EditsaleManModal"
                        data-callback="populateSalemanForm">
                    @csrf
                <input type="hidden" name="_method" value="PUT">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Sale Person</h5>
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
                                                        <label>Sale Person Name <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            </div>
                                                            <input type="text" value="{{ old('name') }}" name="name" class="form-control name">
                                                        </div>
                                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="form-label">Sale Person Status</label><br>
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
