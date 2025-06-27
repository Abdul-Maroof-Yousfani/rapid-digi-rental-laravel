@extends('admin.master-main')
@section('content')
<style>
    .spinner-border.custom-blue {
        width: 3rem;
        height: 3rem;
        border-width: 0.4rem;
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: #0d6efd;
        border-left-color: rgba(13, 110, 253, 0.25);
    }

    .table-scroll {
        max-height: 800px; /* ya jitni height chahiye */
        overflow-y: auto;
    }
</style>

<div class="main-content">
    <section class="section">
        <div class="section-body">
            <!-- Filters -->
            <form method="get" id="investorVehicleReportForm" class="mb-4">
                <div class="form-row align-items-end">
                    <div class="col-md-3">
                        <label for="from_date">From</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date">To</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-4 w-100">Filter</button>
                    </div>
                </div>
            </form>


            <!-- Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-scroll">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" style="width:100%;">
                                        <thead  style="background: #f8f8f8">
                                            <tr>
                                                <th>Agreement No</th>
                                                <th>Customer</th>
                                                <th>Invoice No</th>
                                                <th>Vehicle</th>
                                                <th>Booking Dates</th>
                                                <th>Price</th>
                                                <th>Transaction Type</th>
                                            </tr>
                                        </thead>
                                        <tbody id="investorVehicleReportList"> </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
