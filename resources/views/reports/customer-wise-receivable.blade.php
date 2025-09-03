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
            max-height: 800px;
            /* ya jitni height chahiye */
            overflow-y: auto;
        }

        .table-container {
            max-height: 400px;
            /* Scrollable height */
            overflow-y: auto;
            position: relative;
            /* Ensure sticky elements respect container */
        }

        table thead th {
            position: sticky;
            top: 0;
            background-color: #f3f2f2ff !important;
            /* Solid background */
            z-index: 2;
            /* Works only with position + background */
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <!-- Filters -->
                <form method="get" id="customerWiseReceivableReportForm" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="col-md-2">
                            <label for="from_date">From</label>
                            <input type="date" name="from_date" id="from_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="to_date">To</label>
                            <input type="date" name="to_date" id="to_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="customer_id">Customer</label>
                            <select name="customer_id" class="form-control select2" id="customer_id">
                                <option value="">Select Customer</option>
                                @foreach ($customers as $item)
                                    <option value="{{ $item->id }}">{{ $item->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary mt-4 w-100">Filter</button>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-primary prinn pritns" onclick="printView('printReport','','1')" style="">
                                <span class="glyphicon glyphicon-print"></span> Print
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-scroll">
                                    <div class="table-responsive" id="printReport">
                                        {{-- <table class="table table-bordered table-hover p-0" id="" style="width:100%;">
                                            --}}
                                            <center>
                                                <div class="soa-report-header mb-3">
                                                    <h2>Customer Wise Receivable Report</h2>

                                                </div>
                                            </center>
                                            <div class="table-container">

                                                <table class="table table-bordered table-sm" style="width:100%;">
                                                    <thead style="background: #f8f8f8">
                                                        <tr>
                                                            <th>S No.</th>
                                                            <th>Agreement no.</th>
                                                            <th>Invoice no.</th>
                                                            <th>Customer</th>
                                                            <th class="text-center">Booking Total</th>
                                                            <th class="text-center">Paid </th>
                                                            <th class="text-center">Receivable</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="customerWiseReceivableReportList"> </tbody>
                                                </table>
                                            </div>
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