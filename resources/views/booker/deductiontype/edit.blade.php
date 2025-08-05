@extends('admin.master-main')
@section('content')

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        <div class="section-body">
            <form action="{{ url('invoice-type/'.$type->id) }}" method="post">
                @csrf
                @method('put')
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card-body">
                            <div class="col-md-6">
                                <h3>Edit Invoice Type</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <input type="text" value="{{ $type->name }}" name="name" class="form-control">
                                    </div>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1" class="selectgroup-input-radio"
                                                {{ $type->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">Active</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0" class="selectgroup-input-radio"
                                                {{ $type->status == 0 ? 'checked' : '' }}>
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
                        <input type="submit" value="Update Invoice Type" name="submit" class="btn btn-primary">
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