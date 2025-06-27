@extends('admin.master-main')
@section('content')
    <style>
        .table-scroll {
            max-height: 800px;
            overflow-y: auto;
        }
        .spinner-border.custom-blue {
            width: 3rem;
            height: 3rem;
            border-width: 0.4rem;
            border-top-color: #0d6efd;
            border-right-color: #0d6efd;
            border-bottom-color: #0d6efd;
            border-left-color: rgba(13, 110, 253, 0.25);
        }

        /* Print-specific styles */
        @media print {
            .table-scroll {
                max-height: none !important;
                overflow: visible !important;
            }
            .print-heading {
                display: block !important;
                margin-top: 5px !important;
                margin-bottom: 0px !important;
                font-size: 25px;
                color: #000;
            }

            .print-footer {
                display: block !important;
                padding: 5px 0;
                font-size: 25px;
                color: #000;
                text-align: justify;
            }

            .print-footer-table {
                display: table !important;
                margin:auto !important;
                border-collapse: collapse;
                width: 70% !important;
                font-size: 18px;
                color: #000 !important;
            }

            .print-footer-table td {
                border: 2px solid #000 !important;
                padding: 8px 12px;
                text-align: left;
            }

            .print-footer-table td:last-child {
                text-align: right;
                font-weight: bold;
            }

            .main-sidebar,
            .navbar,
            .btn,
            .form-row,
            .section-header,
            footer,
            .card-header {
                display: none !important;
            }

            .form-row,
            .dataTables_filter {
                display: none !important;
            }

            input[type="search"],
            label[for="search"],
            .search-container {
                display: none !important;
            }

            .totals {
                display: none !important;
            }

            /* Hide DataTables info, length, and pagination controls */
            .dataTables_info,
            .dataTables_length,
            .dataTables_paginate {
                display: none !important;
            }

            .print-header {
                display: flex !important;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                border-bottom: 1px solid #000;
                padding-bottom: 10px;
            }

            .main-content,
            .section,
            .section-body {
                margin: 0;
                padding: 0;
            }

            #tableExport {
                display: table !important;
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                margin: 0 auto !important;
                font-size: 14px;
                border-collapse: collapse;
                width: 70% !important;

                border-top: 3px solid #000;
                border-right: 2px solid #000;
                border-left: 2px solid #000;

            }

            #tableExport th,
            #tableExport td {
                border: 2px solid #000 !important;
                padding: 8px;
                box-sizing: border-box;
                min-width: 50px;
                text-align: left !important;

                /* Minimum width to prevent collapse */
            }

            #tableExport thead{
                border: 3px solid #000 !important;
                font-weight: bold !important;
            }

            #tableExport th {
                background-color: #f2f2f2;
                border-right: 4px solid #000 !important;
                border-left: 3px solid #000 !important;
                border-bottom: 2px solid #000 !important;
                /* Distinguish header */
                font-weight: bold;
                text-align: center;
                font-size: 18px;
            }

            #tableExport td {
                display: table-cell !important;
                font-size: 18px;
                text-align: left !important;
            }

            #tableExport td:nth-child(3){
                text-align: right !important;
            }
            #tableExport td:nth-child(4){
                text-align: right !important;
            }

            /* Ensure table-responsive and card don't interfere */
            .table-responsive {
                overflow: visible !important;
                /* Prevent overflow hiding borders */
            }

            .card {
                border: none !important;
                /* Remove card border interference */
            }

            .totals {
                text-align: right;
                margin-top: 20px;
                font-size: 14px;
            }
        }

        /* Default hidden print header, heading, footer and footer table */
        .print-header,
        .print-heading,
        .print-footer,
        .print-footer-table {
            display: none;
            padding: 50px;
        }

        .print-footer-table {
            padding: 0;

        }

        .footer-table-data-head {
            font-size: 18px !important;
            text-align: center !important;
        }

        .print-header .logo {
            max-height: 60px;
        }

        .print-header .print-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.5;
        }

        .print-regards {
            margin-top: 25px !important;
            font-size: 23px !important;
            padding-left: 30px !important;
            color: #000 !important;
        }

        @media screen {

            .print-heading,
            .print-footer,
            .print-footer-table,
            .print-regards {
                display: none;
            }
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <!-- Print Header: Logo Left, Info Right -->
                <div class="print-header">
                    <div class="logo">
                        <img src="{{ asset('assets/img/Report-print-logo.png') }}" alt="Rapid Rentals Logo"
                            style="max-height: 60px;">
                    </div>
                    <div class="print-info">
                        <strong>RAPID RENTALS - FZCO 34271-001</strong><br>
                        IFZA BUSINESS PARK DDP SILICON OASIS – DUBAI U.A.E<br>
                        TRN: 104137158200003
                    </div>
                </div>

                <!-- Print Heading: Shown only in print -->
                <div class="print-heading">
                    <h2 style="text-align: center; text-decoration: underline; font-weight: bold;">
                        Statement of Account for the month of June 2024
                    </h2>
                    <p style="margin-top: 80px;">15.07.2024</p>
                    <p>Dear Mr. ,</p>
                    <p>
                        This is to inform you in regard to the investment towards car rental with Rapid Rentals Dubai FZ
                        LLC.
                        As for the month of June till 15-07-2024 we have rented your vehicles and the details shared below
                        along with the rental that has been charged to our customers.
                    </p>
                </div>

                <!-- Filters -->
                <form method="get" id="soaReportForm" class="mb-4">
                    <div class="form-row align-items-end">
                        {{-- <div class="col-md-3">
                            <label for="month">Month</label>
                            <input type="month" name="month" id="month" class="form-control" value="">
                        </div> --}}
                        <div class="col-md-2">
                            <label for="from_date">From</label>
                            <input type="date" name="from_date" id="from_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="to_date">To</label>
                            <input type="date" name="to_date" id="to_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="investor_id">Investor</label>
                            <select name="investor_id" class="form-control select2" id="investor_id">
                                <option value="">Select Investor</option>
                                @foreach ($investor as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
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
                                        <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Plate no.</th>
                                                    <th>Car Make - Model & Year</th>
                                                    <th>Rental Period</th>
                                                    <th>Rental Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="soaReportList">
                                                <tr>
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
                {{-- <div class="totals">
                    <p>Total Amount as per rental period: <span id="totalAmount"></span></p>
                    <p>Amount after service fee deduction %: <span id="netAmount"></span></p>
                </div> --}}

                <!-- Print Footer: Shown only when printing -->
                <div class="print-footer">
                    <p>
                        Kindly acknowledge the payment advice by confirming with a reply to the email and as per the
                        contract 20% will be
                        deducted from the below stated amount as service fee and 80% shall be credited into your personal
                        bank account.<br /><br />
                        For any queries or concern please feel free to write back to us.
                    </p>
                </div>

                <!-- Print Footer Table: Shown only when printing -->
                <table class="print-footer-table">
                    <tr>
                        <td class="footer-table-data-head">Total Amount as per rental period</td>
                        <td><span id="printTotalAmount"></span></td>
                    </tr>
                    <tr>
                        <td class="footer-table-data-head">Amount after service fee deduction <span class="agreePercentage"></span>%</td>
                        <td><span id="printNetAmount">15,862</span></td>
                    </tr>
                </table>
                <div class="print-regards">
                    <p>Best Regards,<br />
                        Thijs Schrijver (Founder)<br />
                        Rapid Rentals Dubai FZ LLC</p>
                </div>

            </div>
        </section>
    </div>
@endsection
