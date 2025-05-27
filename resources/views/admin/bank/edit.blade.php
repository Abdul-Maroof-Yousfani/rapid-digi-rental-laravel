@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @can('manage customers')
                <form action="{{ url('admin/bank/'.$bank->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card-body">
                                <div class="col-md-6">
                                    <h3>Edit Bank</h3>
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
                                        <input type="text" value="{{ $bank->bank_name }}" name="bank_name" class="form-control" required>
                                        @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Account no. <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $bank->account_number }}" name="account_no" class="form-control account_no" required>
                                        @error('account_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Branch </label>
                                        <input type="text" value="{{ $bank->branch }}" name="branch" class="form-control branch">
                                    </div>


                                    <div class="form-group">
                                        <label>Swift Code </label>
                                        <input type="text" value="{{ $bank->swift_code }}" name="swift_code" class="form-control swift_code">
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
                                            <input type="text" value="{{ $bank->account_name }}" name="account_name" class="form-control"  required>
                                        </div>
                                        @error('account_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>IBAN <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ $bank->iban }}" name="iban" class="form-control iban"  required>
                                        @error('iban')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Currency </label>
                                        <select name="currency" class="form-control select2">
                                            <option value="">Select Currency</option>
                                            <option>PKR</option>
                                            <option>AED</option>
                                            <option>USD</option>
                                            <option>UAE</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Notes </label>
                                        <textarea name="notes" cols="30" class="form-control" rows="10">{{ $bank->notes }}</textarea>
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Edit Bank" name="submit" class="btn btn-primary">
                        </div>
                    </div>
                </form>
                @endcan
            </div>
        </section>
    </div>
@endsection
