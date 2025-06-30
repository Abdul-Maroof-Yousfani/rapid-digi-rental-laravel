@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first() . ' ' . 'Portal'))
@section('content')

Credit Note View
<style>
    @media print{body *{visibility:hidden !important;}
        .print-area,.print-area *{visibility:visible !important;}
        .print-area{position:absolute;left:0;top:0;width:100%;}
        /* Ensure background colors are printed */
        .thead-light{background-color:#2F81B7 !important;-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important;}
        .thead-light th{color:#ffffff !important;-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important;}
        /* Target the Balance Due row specifically */
        tr.bg-light{background-color:#f8f9fa !important;-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important;}
        tr.bg-light th,tr.bg-light td{background-color:#f8f9fa !important;-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important;padding-right:0 !important;/* Remove padding to minimize gap */
        padding-left:0 !important;/* Remove padding to minimize gap */
        margin:0 !important;/* Ensure no margins */
        }
        /* Ensure table layout is tight */
        .row.justify-content-end .table{border-spacing:0 !important;border-collapse:collapse !important;}
        /* Optional:Adjust column widths to bring text closer */
        tr.bg-light th{width:auto !important;min-width:0 !important;}
        tr.bg-light td{width:auto !important;min-width:0 !important;}
        /* Hide sidebar,navbar,etc. */
        .sidebar,.navbar,.btn,.no-print{display:none !important;}
    }

 .cred1{display:flex;justify-content:space-between;}

 </style>

     <!-- Main Content -->
    <div class="main-content">

        <!-- Print Button (outside print area, so not printed) -->
        <div class="text-right mb-3 no-print">
            <button onclick="printInvoice()" class="btn btn-primary">Print Invoice</button>
        </div>

        <section class="section print-area">
            <div class="container my-5 border p-4 bg-white">
                <!-- Header -->
                <div style="margin-bottom: 5rem" class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h5 class="font-weight-bold text-dark">Credit note</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-left"></div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right">
                        <h3 style="color:#33A1E0; font-size:3rem; font-weight:100">LOGO</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-left">
                        <p class="mb-0 text-dark">Your business same. your business address </p>
                    </div>
                </div>
                <br>
                 <!-- Bill To & Invoice Info -->
                 <div class="row mb-3">
                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8 text-left">
                             <p class="mb-0 font-weight-bold text-dark">Bill TO</p>
                            <p class="mb-0 font-weight-bold text-dark">Your clients name</p>
                            <p class="mb-0 font-weight-bold text-dark">Your clients adddress</p>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right">
                            <div class="cred1">
                                <p class="mb-0 font-weight-bold text-dark">Credit date no..</p>
                                <p class="mb-0 font-weight-bold text-dark">2022808</p>
                            </div>
                            <div class="cred1">
                                <p class="mb-0 font-weight-bold text-dark">Issue date</p>
                                <p class="mb-0 font-weight-bold text-dark">14/7/2024</p>
                            </div>
                            <div class="cred1">
                                <p class="mb-0 font-weight-bold text-dark">Referance</p>
                                <p class="mb-0 font-weight-bold text-dark">2022006</p>
                            </div>
                        </div>
                  </div>

               
                <!-- Table -->
                <table class="table align-middle">
                    <thead class="thead-light">
                        <tr class="text-white">
                            <th style="background-color: #316125 !important" class="text-white text-left">Credit Date No.<br> <span>2022006</span></th>
                            <th style="background-color: #316125 !important" class="text-white text-left">Issue Date<br> <span>306/6/2024</span></th>
                            <th style="background-color: #316125 !important " class="text-white text-left">Due Date<br> <span>14/7/2024</span></th>
                            <th colspan="4" style="background-color: #000 !important" class="text-white text-left">Total Date (PKR)<br> <span>Rs 0.00</span></th>
                        </tr>
                    </thead>
                </table>


                <table class="table align-middle">
                    <thead class="thead-light2">
                        <tr class="text-Black">
                            <th style=" background:transparent; color:#000;" colspan="7" class="text-left">Description</th>
                            <th style=" background:transparent; color:#000;" class="text-center">Quantity</th>
                            <th style=" background:transparent; color:#000;" class="text-right">Unit Price ($)</th>
                            <th style=" background:transparent; color:#000;" class="text-right">Amount ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                      
                            <tr style="border-top: 1px solid #ADADAD;">
                                <td  colspan="7" class="text-left">tax%</td>
                                <td class="text-center">0</td>
                                <td class="text-right">0.000</td>
                                <td class="text-right">Rs 0.000</td>
                            </tr>
                           <tr style="border-top: 1px solid #ADADAD;">
                                <td  colspan="7" class="text-left">tax%</td>
                                <td class="text-center">0</td>
                                <td class="text-right">0.000</td>
                                <td class="text-right">Rs 0.000</td>
                            </tr>
                           <tr style="border-top: 1px solid #ADADAD;">
                                <td  colspan="7" class="text-left">tax%</td>
                                <td class="text-center">0</td>
                                <td class="text-right">0.000</td>
                                <td class="text-right">Rs 0.000</td>
                            </tr>
                            <tr style="border-top: 1px solid #ADADAD;">
                                <td  colspan="7" class="text-left">Total (RS)</td>
                                <td class="text-center"></td>
                                <td class="text-right"></td>
                                <td class="text-right">Rs 0.000</td>
                            </tr>
                    
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="row justify-content-end">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <table class="table">
                            <tr>
                                <th class="text-right">Issued by, signature</th>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div style="margin-top: 10rem" class="text-dark">
                    <div class="nots text-right">
                        <h6>Notes</h6>
                    </div>
                    <hr>
                    <p class="mb-0">Thank you for your business.</p>
                    <p class="mb-0">DEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.</p>
                </div>
               
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
