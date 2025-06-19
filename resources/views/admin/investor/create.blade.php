
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <form action="{{ url('admin/investor') }}" method="post">
                @csrf
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                          <div class="col-md-6">
                              <h3>Add Investor</h3>
                          </div>
                      </div>
                  </div>
              </div>
                <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                      <div class="card-body">
                          <div class="form-group">
                              <label>Investor name <span class="text-danger">*</span></label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <input type="text" value="{{ old('investor_name') }}" name="investor_name" class="form-control">
                              </div>
                              @error('investor_name') <span class="text-danger">{{ $message }}</span> @enderror
                          </div>
                          <div class="form-group">
                              <label>Email <span class="text-danger">*</span></label>
                              <div class="input-group">
                                  <div class="input-group-prepend">
                                      <div class="input-group-text">
                                          <i class="fas fa-envelope"></i>
                                      </div>
                                  </div>
                                  <input type="email" value="{{ old('email') }}" name="email" class="form-control email">
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
                            <label>Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text">
                                      <i class="fas fa-lock"></i>
                                  </div>
                                </div>
                                <input id="password-confirm" type="password" name="password_confirmation" class="form-control"  autocomplete="new-password">
                            </div>
                            @error('password_confirmation') <span class="text-danger">{{ $message }}</span> @enderror
                          </div>


                          <div class="form-group">
                              <label>Phone Number <span class="text-danger">*</span></label>
                              <div class="input-group">
                                  <div class="input-group-prepend">
                                      <div class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                      </div>
                                  </div>
                                  <input type="number" value="{{ old('phone') }}" name="phone" class="form-control">
                              </div>
                              @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                          </div>



                        <div class="form-group">
                          <label>C-NIC  <span class="text-danger">*</span></label>
                          <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card"></i>
                                </div>
                              </div>
                              <input type="text" value="{{ old('cnic') }}" name="cnic" class="form-control cnic">
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
                          <label>Date Of Birth  </label>
                          <input type="date" value="{{ old('dob') }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
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
                            <input type="text" name="postal_code" class="form-control postal_code">
                        </div>

                        <div class="form-group">
                          <label>Address  <span class="text-danger">*</span></label>
                          <textarea name="address" cols="30" class="form-control" rows="10">{{  old('address')  }}</textarea>
                          @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Gender  <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-venus-mars"></i>
                                    </div>
                                </div>
                                <select name="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Status</label>
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

                    </div>
                    </div>


                </div>
                </div>
                <div class="row">
                  <div class="col-12 col-md-6 col-lg-6">
                    <input type="submit" value="Add Investor" name="submit" class="btn btn-primary">
                  </div>
                </div>
            </form>
          </div>
        </section>
      </div>



@endsection
