@extends('admin.master-main')
@section('content')
<style>
    .disableClick {
        cursor: not-allowed !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__arrow,
    .select2-container--default .select2-selection--single .select2-selection__arrow {

        width: 16px !important;

    }

    .table-responsive {
        overflow: scroll;
        white-space: nowrap
    }
</style>

@php

@endphp

<!-- Main Content -->
<div class="main-content">
    <section class="section">

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card-body">
                        <h3 class="mb-0">Invoice Type</h3>
                    </div>
                </div>
            </div>
            <form action="{{ url('invoice-type') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" value="" placeholder="Name" name="name" class="form-control name" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
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
                        <input type="submit" value="Submit Invoice Type" name="submit" class="btn btn-primary">
                    </div>
                </div>

            </form>

        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{ session('
        error ') }}',
    });
</script>
@endif

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('
        success ') }}',
    });
</script>
@endif

@endsection

@section('script')
<script>
    $(document).ready(function() {


    });
</script>
@endsection