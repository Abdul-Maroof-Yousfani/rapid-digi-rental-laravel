
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
          @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
          <div class="section-body">
            <form action="{{ url('admin/booker/'.$booker->id) }}" method="post">
              @csrf
              @method('PUT')
              <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card-body">
                      <div class="col-md-6">
                        <h3>Edit Booker</h3>
                      </div>
                    </div>
                </div>
              </div>
              <div class="row">
              <div class="col-12 col-md-6 col-lg-6">
                  <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Booker name <span class="text-danger">*</span></label>
                            <input type="text" value="{{ $booker->name }}" name="booker_name" class="form-control">
                            @error('booker_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                                <input type="email" value="{{ $booker->user->email }}" name="email" class="form-control email">
                            </div>
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text">
                                      <i class="fas fa-lock"></i>
                                  </div>
                                </div>
                                <input id="password" type="password" name="password" class="form-control"  autocomplete="new-password">
                            </div>
                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                    </div>
                                </div>
                                {{-- <input type="number" value="{{ $booker->phone }}" name="phone" class="form-control"> --}}
                                <input type="tel" value="{{ $booker->phone }}" id="uaePhone" name="phone" class="form-control" placeholder="+971-xx-xxxxxxx" maxlength="17">
                            </div>
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>


                      <div class="form-group">
                        <label>C-NIC  <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                            <div class="input-group-text">
                                $
                            </div>
                            </div>
                            <input type="text" value="{{ $booker->cnic }}" name="cnic" class="form-control cnic">
                        </div>
                        @error('cnic') <span class="text-danger">{{ $message }}</span> @enderror
                      </div><br>
                    </div>
                  </div>

              </div>
              <div class="col-12 col-md-6 col-lg-6">
                  <div class="card">
                  <div class="card-body">
                      <div class="form-group">
                        <label>Date Of Birth </label>
                        <input type="date" value="{{ $booker->dob }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                      </div>
                      {{-- <div class="form-group">
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
                      </div> --}}
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
                          <input type="text" value="{{ $booker->postal_code }}" name="postal_code" class="form-control postal_code">
                      </div>

                      <div class="form-group">
                        <label>Address  <span class="text-danger">*</span></label>
                        <textarea name="address" cols="30" class="form-control" rows="10">{{  $booker->address  }}</textarea>
                        @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                      </div>


                      <div class="form-group">
                        <label>Gender  <span class="text-danger">*</span></label>
                        <select name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="male" {{  $booker->gender=='male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{  $booker->gender=='female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                      </div>

                      <div class="form-group">
                          <label class="form-label">Booker Status</label>
                          <div class="selectgroup w-100">
                          <label class="selectgroup-item">
                              <input type="radio" name="status" value="1" class="selectgroup-input-radio" {{ $booker->status==1 ? 'checked' : '' }}>
                              <span class="selectgroup-button">Active</span>
                          </label>

                          <label class="selectgroup-item">
                              <input type="radio" name="status" value="0" class="selectgroup-input-radio" {{ $booker->status==0 ? 'checked' : '' }}>
                              <span class="selectgroup-button">Inactive</span>
                          </label>
                          </div>
                      </div>

                  </div>
                  </div>


              </div>
              </div>
              <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                  <input type="submit" value="Add Booker" name="submit" class="btn btn-primary">
                </div>
              </div>
          </form>
          </div>
        </section>

      </div>



@endsection

@section('script')

    <script src="{{ asset('assets/js/forms-format.js') }}"></script>

@endsection
