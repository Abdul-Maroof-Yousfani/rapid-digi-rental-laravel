@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <form action="{{ url('admin/bank') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Add Bank</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Bank name <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ old('bank_name') }}" name="bank_name" class="form-control" required>
                                        @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Account no. <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ old('account_no') }}" name="account_no" class="form-control account_no" required>
                                        @error('account_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Branch </label>
                                        <input type="text" name="branch" class="form-control branch">
                                    </div>


                                    <div class="form-group">
                                        <label>Swift Code </label>
                                        <input type="text" name="swift_code" class="form-control swift_code">
                                    </div><br>

                                </div>
                            </div>

                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Account Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" value="{{ old('account_name') }}" name="account_name" class="form-control"  required>
                                        </div>
                                        @error('account_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>IBAN <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('iban') }}" name="iban" class="form-control iban"  required>
                                        @error('iban')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Currency </label>
                                        <select name="currency" class="form-control select2">
                                            <option value="">Select Currency</option>
                                            <option>PKR</option>
                                            <option selected>AED</option>
                                            <option>USD</option>
                                            <option>UAE</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Notes </label>
                                        <textarea name="notes" cols="30" class="form-control" rows="10">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Add Bank" name="submit" class="btn btn-primary">
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
