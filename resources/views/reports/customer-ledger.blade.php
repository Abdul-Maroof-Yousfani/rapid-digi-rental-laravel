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
                <form method="get" id="customerLedgerReportForm" class="mb-4">
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
                            <button type="button" class="btn btn-success" id="exportExcelBtn" style="margin-right: 10px;">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
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
                              <form class="filterForm">
                                    <div class="row">
                                        <div class="col-3 ml-auto">
                                            <input type="text" placeholder="Search Invoice No. / Customer" class="form-control"
                                                id="search">
                                        </div>
                                    </div><br>
                                </form>
                                <div class="table-scroll">
                                    <div class="table-responsive" id="printReport">
                                        {{-- <table class="table table-bordered table-hover p-0" id="" style="width:100%;">
                                            --}}
                                            <center>
                                                <div class="soa-report-header mb-3">
                                                    <h2>Customer Ledger Report</h2>

                                                </div>
                                            </center>
                                            <div class="table-container">

                                                <table class="table table-bordered table-sm" style="width:100%;">
                                                    <thead style="background: #f8f8f8">
                                                        <tr>
                                                            
                                                            <th>Date</th>
                                                            <th>Invoice Number</th>
                                                            <th>Description</th>
                                                            <th class="text-center">Item Desc</th>
                                                            <th class="text-center">Invoice Amount</th>
                                                            <th class="text-center">Payment received</th>
                                                            <th class="text-center">Outstanding</th>
                                                            <th class="text-center">Invoice Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="customerLedgerReportList"> </tbody>
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

@section('script')
<script>
    $(document).ready(function() {
        // Export to Excel button click handler
        $('#exportExcelBtn').on('click', function() {
            // Get form values
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var customerId = $('#customer_id').val();
            
            // Build query string
            var params = new URLSearchParams();
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            if (customerId) params.append('customer_id', customerId);
            
            // Create export URL
            var exportUrl = '{{ route("customerLedger.export") }}?' + params.toString();
            
            // Open in new window to trigger download
            window.location.href = exportUrl;
        });
    });

    $(document).ready(function () {
        let searchTimeout;
        $('#search').on('keyup', function () {
            clearTimeout(searchTimeout);
            let search = $(this).val();
            
            // Show loading spinner
            $('#customerLedgerReportList').html(`
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);
            
            // Debounce search to avoid too many requests
            searchTimeout = setTimeout(function() {
                // Get date values from form inputs
                let fromDate = $('#from_date').val();
                let toDate = $('#to_date').val();
                
                $.ajax({
                    url: '/search-customer-ledger',
                    method: 'get',
                    data: {
                        search: search,
                        from_date: fromDate,
                        to_date: toDate
                    },
                    success: function (response) {
                        let html = '';
                        let totalInvoiceAmount = 0;
                        let totalPaymentReceive = 0;
                        let totalOutstanding = 0;
                        let countedInvoiceIds = [];
                        
                        if (response.ledgerData && response.ledgerData.length > 0) {
                            $.each(response.ledgerData, function (index, item) {
                                // Only add invoice_amount once per invoice_id
                                if (item.invoice_id && countedInvoiceIds.indexOf(item.invoice_id) === -1) {
                                    totalInvoiceAmount += parseFloat(item.invoice_amount || 0);
                                    countedInvoiceIds.push(item.invoice_id);
                                }
                                
                                totalPaymentReceive += parseFloat(item.payment_receive || 0);
                                totalOutstanding += parseFloat(item.outstanding || 0);
                                
                                let invoiceLink = '';
                                if (item.invoice_id && item.invoice_number) {
                                    invoiceLink = `<a href="/booking/view-invoice/${item.invoice_id}" target="_blank" style="color: #0d6efd; text-decoration: underline;">${item.invoice_number || ''}</a>`;
                                } else {
                                    invoiceLink = item.invoice_number || '';
                                }
                                
                                html += `
                                    <tr>
                                        <td>${item.date || ''}</td>
                                        <td>${invoiceLink}</td>
                                        <td>${item.description || ''}</td>
                                        <td>${item.item_desc || ''}</td>
                                        <td align="right">${parseFloat(item.invoice_amount || 0).toFixed(2)}</td>
                                        <td align="right">${parseFloat(item.payment_receive || 0).toFixed(2)}</td>
                                        <td align="right">${parseFloat(item.outstanding || 0).toFixed(2)}</td>
                                        <td>${item.invoice_status || ''}</td>
                                    </tr>
                                `;
                            });
                            
                            // Add subtotal row
                            html += `
                                <tr>
                                    <td colspan="4" align="right"><b>Sub Total</b></td>
                                    <td align="right"><b>${totalInvoiceAmount.toFixed(2)}</b></td>
                                    <td align="right"><b>${totalPaymentReceive.toFixed(2)}</b></td>
                                    <td align="right"><b>${(totalInvoiceAmount - totalPaymentReceive).toFixed(2)}</b></td>
                                    <td></td>
                                </tr>
                            `;
                        } else {
                            html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                        }
                        
                        $('#customerLedgerReportList').html(html);
                    },
                    error: function(xhr, status, error) {
                        $('#customerLedgerReportList').html(`
                            <tr>
                                <td colspan="8" class="text-center text-danger">Error loading data. Please try again.</td>
                            </tr>
                        `);
                    }
                });
            }, 500); // 500ms debounce
        });
    });
</script>
@endsection