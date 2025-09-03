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
    </style>

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <!-- Filters -->
              <div class="mb-3 d-flex justify-content-between">
    <button onclick="history.back()" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </button>
    
    <button class="btn btn-primary prinn pritns" onclick="printView('printReport','','1')">
        <span class="glyphicon glyphicon-print"></span> Print
    </button>
</div>

                {{-- <div class="mb-3 text-right">
                       
                    </div> --}}
                <!-- Table -->
                <div class="row" id="printReport">
                    
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="table-scroll">
                                    <div class="table-responsive" id="printReport">
                                        {{-- <table class="table table-bordered table-hover p-0" id="" style="width:100%;">
                                            --}}
                                            <center>
                                                <div class="soa-report-header mb-3">
                                                    <h2>Customer Wise Sales Detail Report</h2>

                                                </div>
                                            </center>
                                            <table class="table table-bordered table-sm" style="width:100%;">
                                                <thead style="background: #f8f8f8">
                                                    <tr>
                                                        <th>S No.</th>
                                                        <th>Customer.</th>
                                                        <th>Invoice no.#</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid Amount</th>
                                                        <th>Pending Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $number = 1;
                                                        $subtot = 0;
                                                        $subamt = 0;
                                                        $subpnd = 0;
                                                    @endphp
                                                    @foreach ($booking as $item)
                                                        @php

                                                            $price = $item->bookingData()->first()?->price ?? 0;
                                                            $paidAmount = $item->payment?->paid_amount ?? 0;
                                                            if ($price <= $paidAmount) {
                                                                $paid_amt = $price;
                                                                $rece_amt = 0;
                                                            } else if ($price > $paidAmount) {
                                                                $paid_amt = $paidAmount;
                                                                $rece_amt = $price - $paidAmount;
                                                            }

                                                            $childCount = $item->bookingData->count();
                                                            $itemTotal = $item->item_total ?? 0;
                                                            $paidAmount = $paidAmount ?? 0;
                                                            $pendingAmount = $item->payment->pending_amount ?? 0;

                                                            $subtot += $price;
                                                            $subamt += $paid_amt;
                                                            $subpnd += $rece_amt;
                                                        @endphp

                                                        {{-- Parent Row --}}
                                                        <tr>
                                                            {{-- S. No with rowspan --}}
                                                            <td class="align-middle">{{ $number }}.</td>
                                                            <td>{{ $item->customer->customer_name ?? 'N/A' }} |
                                                                {{ $item->agreement_no }}
                                                            </td>
                                                            <td>
                                                                {{ $item->bookingData->pluck('invoice.zoho_invoice_number')->first() }}
                                                            </td>
                                                            <td class="text-right">{{ number_format($price, 2) }}</td>
                                                            <td class="text-right">{{ number_format($paid_amt, 2) }}</td>
                                                            <td class="text-right">{{ number_format($rece_amt, 2) }}</td>
                                                        </tr>



                                                        @php $number++; @endphp
                                                    @endforeach

                                                    {{-- Footer total row --}}
                                                    <tr class="text-right">
                                                        <td colspan="3"><b>Sub Total</b></td>
                                                        <td>{{ number_format($subtot, 2) }}</td>
                                                        <td>{{ number_format($subamt, 2) }}</td>
                                                        <td>{{ number_format($subpnd, 2) }}</td>
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
        </section>
    </div>
@endsection