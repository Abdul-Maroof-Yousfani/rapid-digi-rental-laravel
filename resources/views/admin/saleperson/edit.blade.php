
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <form action="{{ url('admin/sale-person/'.$salePerson->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <h3>Edit Sale Person</h3>
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
                                    <label>Sale Person Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <input type="text" value="{{ $salePerson->name }}" name="name" class="form-control name">
                                    </div>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sale Person Status</label>
                                    <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="status" value="1" class="selectgroup-input-radio" {{ $salePerson->status==1 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">Active</span>
                                    </label>

                                    <label class="selectgroup-item">
                                        <input type="radio" name="status" value="0" class="selectgroup-input-radio" {{ $salePerson->status==0 ? 'checked' : '' }}>
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
                    <input type="submit" value="Update Sale Person" name="submit" class="btn btn-primary">
                  </div>
                </div>
            </form>
          </div>
        </section>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
