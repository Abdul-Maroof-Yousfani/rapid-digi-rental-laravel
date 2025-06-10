```blade
@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first() . ' ' . 'Portal'))
@section('content')

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            .print-area,
            .print-area * {
                visibility: visible !important;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Ensure background colors are printed */
            .thead-light {
                background-color: #2F81B7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .thead-light th {
                color: #ffffff !important;
                background-color: #2F81B7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Target the Balance Due row specifically */
            tr.bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            tr.bg-light th,
            tr.bg-light td {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding-right: 0 !important;
                /* Remove padding to minimize gap */
                padding-left: 0 !important;
                /* Remove padding to minimize gap */
                margin: 0 !important;
                /* Ensure no margins */
            }

            /* Ensure table layout is tight */
            .row.justify-content-end .table {
                border-spacing: 0 !important;
                border-collapse: collapse !important;
            }

            /* Optional: Adjust column widths to bring text closer */
            tr.bg-light th {
                width: auto !important;
                min-width: 0 !important;
            }

            tr.bg-light td {
                width: auto !important;
                min-width: 0 !important;
            }

            /* Hide sidebar, navbar, etc. */
            .sidebar,
            .navbar,
            .btn,
            .no-print {
                display: none !important;
            }
        }
    </style>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section print-area">
            <div class="container my-5 border p-4 bg-white">
                <!-- Header -->
                <div style="margin-bottom: 5rem" class="row">
                    <div class="col">
                        <h5 class="font-weight-bold text-dark">Rent a Car</h5>
                        <p class="mb-0 text-dark">Pakistan</p>
                        <p class="mb-0 text-dark">testing@company.com</p>
                    </div>
                    <div class="col text-right">
                        <h3 style="color:#33A1E0; font-size:3rem; font-weight:100">Invoice</h3>
                        <p class="mb-0 font-weight-bold text-dark"># INV-000236</p>
                        <p class="mb-0 font-weight-bold text-dark">Balance Due</p>
                        <h5 class="font-weight-bold text-dark">PKR1,001.00</h5>
                    </div>
                </div>

                <!-- Bill To & Invoice Info -->
                <div class="row mb-3">
                    <div class="col">
                        <p class="mb-0 text-dark">Bill To</p>
                        <p class="mb-0 font-weight-bold text-dark">Nerate Sampson</p>
                        <p class="mb-0 text-dark">Commodo quo id quis</p>
                        <p class="mb-0 text-dark">Rawalpindi</p>
                        <p class="mb-0 text-dark">14-Mar-1970 Balochistan</p>
                        <p class="mb-0 text-dark">China</p>
                    </div>
                    <div class="col text-right">
                        <p class="text-dark"><strong>Invoice Date:</strong> 04 Jun 2025</p>
                        <p class="text-dark"><strong>Terms:</strong> Net 15</p>
                        <p class="text-dark"><strong>Due Date:</strong> 19 Jun 2025</p>
                    </div>
                </div>

                <!-- Table -->
                <table class="table align-middle">
                    <thead class="thead-light">
                        <tr style="background-color: #2F81B7" class="text-white">
                            <th class="text-white text-left">#</th>
                            <th class="text-white text-left">Item & Description</th>
                            <th class="text-white text-right">Qty</th>
                            <th class="text-white text-right">Rate</th>
                            <th class="text-white text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 2px solid #ADADAD;">
                            <td class="text-left">1</td>
                            <td class="text-left">
                                KAIYI X3 PRO <br>
                                <small>2025-06-04 TO 2025-06-30</small>
                            </td>
                            <td class="text-right">1.00</td>
                            <td class="text-right">560.00</td>
                            <td class="text-right">560.00</td>
                        </tr>

                        <tr style="border-bottom: 2px solid #ADADAD;">
                            <td class="text-left">2</td>
                            <td class="text-left">
                                Geely Engrand 2025 <br>
                                <small>2025-06-04 TO 2025-06-30</small>
                            </td>
                            <td class="text-right">1.00</td>
                            <td class="text-right">350.00</td>
                            <td class="text-right">350.00</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table">
                            <tr>
                                <td class="text-right">Sub Total</td>
                                <td class="text-right">910.00</td>
                            </tr>
                            <tr>
                                <td class="text-right">VAT (10%)</td>
                                <td class="text-right">91.00</td>
                            </tr>
                            <tr>
                                <th class="text-right">Total</th>
                                <td class="text-right font-weight-bold">PKR1,001.00</td>
                            </tr>
                            <tr class="bg-light text-dark">
                                <th class="text-right">Balance Due</th>
                                <td class="text-right font-weight-bold">PKR1,001.00</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div style="margin-top: 10rem" class="text-dark">
                    <h6>Notes</h6>
                    <p class="mb-0">Thank you for your business.</p>
                    <p class="mb-0">DEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.</p>
                </div>
                <hr>
            </div>
        </section>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Stop form submit
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.status-confirm');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Stop form submit
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, Send It!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
```
