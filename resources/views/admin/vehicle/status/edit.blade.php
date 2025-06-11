
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <form action="{{ url('admin/vehicle-status/'.$status->id) }}" method="post">
                @csrf
                @method('put')
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <h3>Edit Status</h3>
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
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <input type="text" value="{{ $status->name }}" name="name" class="form-control name">
                                    </div>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12 col-md-6 col-lg-6">
                    <input type="submit" value="Update Status" name="submit" class="btn btn-primary">
                  </div>
                </div>
            </form>
          </div>
        </section>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection

