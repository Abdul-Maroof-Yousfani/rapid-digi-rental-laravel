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
                <form method="get" id="customerWiseSalesreportForm" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="col-md-2">
                            <label for="fromDate">From Date</label>
                            <input type="date" name="fromDate" id="fromDate" class="form-control" value="">
                        </div>
                        <div class="col-md-2">
                            <label for="">.</label>
                            <input type="text" placeholder="Between" class="form-control" disabled>
                        </div>
                        <div class="col-md-2">
                            <label for="toDate">To Date</label>
                            <input type="date" name="toDate" id="toDate" class="form-control" value="">
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

                        <div class="col-md-2 text-right">
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
                                                    <h2>Customer Wise Sales Report</h2>

                                                </div>
                                            </center>
                                            <div class="table-container">

                                                <table class="table table-bordered table-sm" style="width:100%;">
                                                    <thead style="background: #f8f8f8">
                                                        <tr>
                                                            <th>S No.</th>
                                                            <th>Customer.</th>
                                                            {{-- <th>Booking no.#</th> --}}
                                                            <th>Total Amount</th>
                                                            <th>Paid Amount</th>
                                                            <th>Pending Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="customerWiseSalesReportList">
                                                        <tr>
                                                            <td></td>
                                                            {{-- <td></td> --}}
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
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