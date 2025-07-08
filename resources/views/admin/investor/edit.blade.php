
@extends('admin.master-main')
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
          <div class="section-body">
            <form action="{{ url('investor/'.$investor->id) }}" method="post">
              @csrf
              @method('put')
                <div class="row">
                  <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                          <div class="col-md-6">
                              <h3>Edit Investor</h3>
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
                              <input type="text" value="{{ $investor->user->name }}" name="investor_name" class="form-control">
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
                                  <input type="email" value="{{ $investor->user->email }}" name="email" class="form-control email">
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
                                <input id="password" type="password" name="password" class="form-control" autocomplete="new-password">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="fas fa-eye toggle-password" style="cursor: pointer;"></i>
                                    </div>
                                </div>
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
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="fas fa-eye toggle-password" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                          <div class="form-group">
                              <label>Phone Number <span class="text-danger">*</span></label>
                              <div class="input-group">
                                  <div class="input-group-prepend">
                                      <div class="input-group-text">
                                      <i class="fas fa-phone"></i>
                                      </div>
                                  </div>
                                  {{-- <input type="number" value="{{ $investor->phone }}" name="phone" class="form-control"> --}}
                                  <input type="tel" value="{{ $investor->phone }}" id="uaePhone" name="phone" class="form-control" placeholder="+971-xx-xxxxxxx" maxlength="17">
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
                              <input type="text" value="{{ $investor->cnic }}" name="cnic" class="form-control cnic">
                          </div>
                          @error('cnic') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                          <label>Agreed Percentage  <span class="text-danger">*</span></label>
                          <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text"> % </div>
                              </div>
                              <input type="number" value="{{ old('agree_percentage') }}" name="agree_percentage" class="form-control agree_percentage" required>
                          </div>
                          @error('agree_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                        </div><br>
                      </div>
                    </div>

                </div>
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                    <div class="card-body">
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
                          <label>Date Of Birth </label>
                          <input type="date" value="{{ $investor->dob }}" name="dob" class="form-control datemask" placeholder="YYYY/MM/DD">
                        </div>
                        <div class="form-group">
                            <label>Country </label>
                            <select name="country" class="form-control select2">
                                <option value="">Select Country</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cabo Verde">Cabo Verde</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo">Congo</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Eswatini">Eswatini</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Greece">Greece</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Laos">Laos</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libya">Libya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova">Moldova</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="North Korea">North Korea</option>
                                <option value="North Macedonia">North Macedonia</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Romania">Romania</option>
                                <option value="Russia">Russia</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="South Sudan">South Sudan</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syria">Syria</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-Leste">Timor-Leste</option>
                                <option value="Togo">Togo</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City">Vatican City</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>
                        <span></span>
                        <div class="form-group">
                            <label>Postal Code </label>
                            <input type="text" value="{{ $investor->postal_code }}" name="postal_code" class="form-control postal_code">
                        </div>

                        <div class="form-group">
                          <label>Address  <span class="text-danger">*</span></label>
                          <textarea name="address" cols="30" class="form-control" rows="10">{{  $investor->address }}</textarea>
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
                                  <option value="male" {{ $investor->gender=="male" ? "selected" : "" }}>Male</option>
                                  <option value="female" {{ $investor->gender=="female" ? "selected" : "" }}>Female</option>
                              </select>
                          </div>
                            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Status</label>
                            <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status" value="1" class="selectgroup-input-radio" {{ $investor->status==1 ? "checked" : "" }}>
                                <span class="selectgroup-button">Active</span>
                            </label>

                            <label class="selectgroup-item">
                                <input type="radio" name="status" value="0" class="selectgroup-input-radio" {{ $investor->status==0 ? "checked" : "" }}>
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
                    <input type="submit" value="Update Investor" name="submit" class="btn btn-primary">
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
