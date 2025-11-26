@extends('admin.master-main')
{{-- @section('title', ucfirst(Auth::user()->getRoleNames()->first() . ' ' . 'Portals')) --}}
@section('title', 'Invoice #' . ($invoice->zoho_invoice_number ?? ''))
@section('content')
    @php
        $userRole = Auth::user()->getRoleNames()->first();
        $subtot = 0;
        foreach ($invoice->bookingData as $item) {
            $subtot += $item->item_total;
            //$paid_amount = $invoice->paymentData()->orderby('id', 'DESC')->first()->paid_amount ?? 0;
            // $pending_amount = $invoice->paymentData()->orderby('id', 'DESC')->first()->pending_amount ?? 0;
            
            $paid_amount = $invoice->paymentData()->sum('paid_amount') ?? 0;
            $pending_amount = $subtot - $paid_amount;

            $due_bal = $pending_amount;
        }
    @endphp

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
                background-color: #00796B !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .thead-light th {
                color: #ffffff !important;
                background-color: #00796B !important;
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

            /* Optional:Adjust column widths to bring text closer */
            tr.bg-light th {
                width: auto !important;
                min-width: 0 !important;
            }

            tr.bg-light td {
                width: auto !important;
                min-width: 0 !important;
            }

            /* Hide sidebar,navbar,etc. */
            .sidebar,
            .navbar,
            .btn,
            .no-print {
                display: none !important;
            }
            
        }

        .bg-white {
            overflow: hidden;
        }

        .box1 {
            margin-top: 6px;
            background: #3e7eac;
            text-align: center;
            width: 16%;
            color: #fff;
            transform: rotateZ(-45deg);
            margin-left: -71px;
            font-size: 14px;
        }

        .sent-status {
            background-color: #3e7eac;
            color: #fff;
        }

        .draft-status {
            background-color: #808080;
            color: #fff;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 120px auto;
            /* 1st column fixed width for labels, 2nd column flexible */
            column-gap: 10px;
            row-gap: 15px;
            /* space between label and value */
            justify-content: end;
            /* aligns whole grid to the right */
        }

        .label {
            text-align: right;
            /* ensures colon stays next to text */
            /* font-weight: bold; */
            font-size: large;
        }

        .value {
            font-size: large;

            text-align: right;
            /* values aligned neatly */
        }
    </style>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Print Button (outside print area, so not printed) -->
       <div class="text-right mb-3 no-print">

    @if ($invoice->invoice_status == 'draft')
        <button class="btn btn-warning" data-toggle="modal" data-target="#markAsSentModal">
            Mark as Sent
        </button>
    @endif

     <button class="btn btn-primary prinn pritns" id="printBtn">
    <span class="glyphicon glyphicon-print"></span> Print
</button>

</div>

<!-- Modal -->
<div class="modal fade" id="markAsSentModal" tabindex="-1" role="dialog" aria-labelledby="markAsSentLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="markAsSentLabel">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Are you sure you want to mark this invoice as <strong>Sent</strong>?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                <!-- Action Button -->
                <form action="{{ route('invoice.markSent', $invoice->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Yes, Mark as Sent</button>
                </form>
            </div>

        </div>
    </div>
</div>

        <section class="section print-area" id="printReport">
            <div class="container p-4 bg-white">
                @php
                    $isOverdue = null;
                    $today = \Carbon\Carbon::today(); // Day after today

                   
                        $endDate = \Carbon\Carbon::parse($invoice->due_date); // convert string to Carbon

                        if (strtolower(trim($invoice->invoice_status)) === 'sent' && $endDate->lt($today)) {
                            $isOverdue = true;
                        }
                    
                @endphp
               <div class="box1 no-print" style="background-color: 
    {{ $isOverdue
        ? '#d3660dff'
        : ($invoice->invoice_status === 'sent'
            ? '#268ddd'
            : (in_array($invoice->invoice_status, ['paid', 'partially paid'])
                ? '#1fcd6d'
                : '#808080')) }}">
    <p>
        {{ ucwords($isOverdue ? 'Overdue' : $invoice->invoice_status) }}
    </p>
</div>





                <br>
                <br>
                <br>
                <!-- Header -->

                <div style="margin-bottom: 5rem" class="row">

                    <div class="col">
                        <a href="{{ route('dashboard') }}"> <img alt="image" style="height: 45%; width:50%;"
                                src="{{ asset('assets/img/logo.png') }}" class="header-logo" /> <span
                                class="logo-name"></span>
                        </a>
                        <h5 class="font-weight-bold mb-0" style="line-height: 1.5; color: #343a40;">Rapid Rentals</h5>
                        <p class="text-dark mb-0" style="line-height: 1.5;">A3, L3, 305-C, IFZA Business Park</p>
                        <p class="text-dark mb-0" style="line-height: 1.5;">Dubai Silicon Oasis</p>
                        <p class="text-dark mb-0" style="line-height: 1.5;">+971 55 452 6880</p>
                        <p class="text-dark mb-0" style="line-height: 1.5;">info@rapidenterprises.ae</p>
                        <p class="text-dark mb-0" style="line-height: 1.5;">www.rapidrentals.ae</p>
                    </div>

                    <div class="col text-right">
                        <h3 style="color:#00796B; font-size:2.3rem; font-weight:50">
                            {{ $invoice->bookingData()->where('tax_percent', '>', 0)->first() ? 'TAX INVOICE' : 'INVOICE' }}
                        </h3>
                        <p class="mb-0 font-weight-bold text-dark"># {{ $invoice->zoho_invoice_number }}</p>
                        <?php if ($invoice->invoice_status != 'draft') { ?>

                        <p class="mb-0 font-weight-bold text-dark"> Balance Due
                        </p>
                        <h5 class="font-weight-bold text-dark">AED{{ number_format($due_bal, 2) }}</h5>
                        <?php } ?>
                    </div>
                </div>

                <!-- Bill To & Invoice Info -->
                <div class="row mb-5">
                    <div class="col align-self-end">
                        <p class="mb-0 text-dark" style="line-height: 1.5;">Bill To</p>
                        @php $customer = $invoice->booking->customer @endphp
                        <p class="mb-0" style="line-height: 1.5;">
                            <a href="{{ route('customer.index') }}"
                                style="font-weight: 900; text-transform: uppercase; color: #343a40; text-decoration: none; font-size: 1rem;">
                                {{ $customer->customer_name }}
                            </a>
                        </p>


                        <!-- <p class="mb-0 text-dark" style="line-height: 1.5;">{{ $customer->address }}</p> -->
                        <p class="mb-0 text-dark" style="line-height: 1.5;">
                            @php
                                $addressParts = array_filter([$customer->address, $customer->city, $customer->state, $customer->country]);
                            @endphp
                            {{ implode(', ', $addressParts) }}
                        </p>

                        <p class="mb-0 text-dark">{{ $customer->phone }}</p>
                        <p class="mb-0 text-dark">{{ $customer->licence }}</p>
                        <!-- <p class="mb-0 text-dark">{{ $customer->country }}</p> -->
                    </div>
                    <div class="col text-right">
                        <div class="grid-container">
                            <div class="text-dark label">Invoice Date:</div>
                            <div class="text-dark value">
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</div>

                            <div class="text-dark label">Due Date:</div>
                            <div class="text-dark value">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-M-Y') }}</div>

                            <div class="text-dark label">Terms :</div>
                            <div class="text-dark value">{{ $invoice->booking->terms ?? '-' }}</div>


                            {{-- <div class="text-dark value">
                                {{ optional(
                                $invoice->bookingData()
                                ->whereNull('deductiontype_id')
                                ->where('booking_id', $item->booking_id)
                                ->first()
                                )->end_date
                                ? \Carbon\Carbon::parse(optional(
                                $invoice->bookingData()
                                ->whereNull('deductiontype_id')
                                ->where('booking_id', $item->booking_id)
                                ->first()
                                )->end_date)->format('d-M-Y')
                                : '-' }}
                            </div> --}}
                            <div class="text-dark label">TRN Number:</div>
                            <div class="text-dark value">{{ $customer->trn_no ?? '-' }}</div>
                            <div class="text-dark label">Sale Person:</div>
                            <div class="text-dark value">{{ $item->booking->salePerson->name ?? '-'}}</div>


                        </div>
                    </div>
                </div>

                <!-- Table -->
                <table class="table align-middle">
                    <thead class="thead-light">
                        <tr style="background-color: #00796B" class="text-white">
                            <th class="text-white text-left">#</th>
                            <th class="text-white text-left">Item & Description</th>
                            <th class="text-white text-right">Qty</th>
                            <th class="text-white text-right">Rate</th>
                            <th class="text-white text-right">Tax</th>
                            {{-- <th class="text-white text-right">Tax Amount</th> --}}
                            <th class="text-white text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach ($invoice->bookingData as $item)
                                            @php $counter++;


                                            @endphp
                                        <tr style="border-bottom: 2px solid #ADADAD;">
                                        <td class="text-left">{{ $counter }}</td>
                                    @php
                                        if ($item->vehicle) {
                                            $vehicleName = $item->vehicle->vehicle_name
                                                ?? trim(($item->vehicle->temp_vehicle_detail ?? '') . ' ' . ($item->vehicle->number_plate ?? ''));
                                        } else {
                                            $vehicleName = $item->invoice_type?->name ?? '';
                                        }
                                    @endphp

                                            <td class="text-left" style="font-family: Arial, sans-serif; padding: 15px;">
                                            <span>
                                                {{ $vehicleName . ' | ' . ($item->vehicle->number_plate ?? '') }}
                                                </span>

                                                <br>
                                                <span>
                                                    {{ $item->description ?: ($item->invoice_type->name ?? '') }}
                                                </span>
<br>
                                                @if(!empty($item->start_date))
                                                    <span>
                                                        {{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') . ' TO ' . \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </td>



                                              <td class="text-right">{{ number_format($item->quantity, 2) ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($item->price, 2) }}</td>
                                            {{-- <td class="text-right">{{ $item->tax_name ?? '-'}}</td> --}}
                                              @php
                                                $subTotal = $item->price * $item->quantity;
                                                $taxAmount = ($subTotal * $item->tax_percent) / 100;
                                            @endphp
                                             <td class="text-right">{{ number_format($taxAmount, 2) }} <br>

                            <small>{{ $item->tax_name ?? '-' }}</small>
                                                    </td>
                                                {{-- <td class="text-right">

                                                    {{ number_format($taxAmount, 2) }}
                                                </td> --}}
                                                <td class="text-right">{{ number_format($item->item_total, 2) }}</td>
                                            </tr>
                        @endforeach
                    <?php
    if ($invoice->bookingData()->orderby('id', 'DESC')->first()->view_type == 1 && $invoice->booking->deposit_type != null) { ?>
                        <tr>
                            <td>{{ $counter + 1 }}</td>
                            <td class="text-left">
                                @if ($invoice->booking->deposit_type == 1)
                                    Cardo
                                @elseif ($invoice->booking->deposit_type == 2)
                                    LPO
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">-</td>
                            <td class="text-right">
                                {{ $invoice->booking->non_refundable_amount }}
                            </td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                                <td class="text-right">
                                {{ $invoice->booking->non_refundable_amount }}
                            </td>

                                @php $subtot += $invoice->booking->non_refundable_amount; @endphp
                            </tr>
                        <?php
    }
                        ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table">
                            <tr>
                                <td class="text-right"><strong>Sub Total</strong></td>
                                <td class="text-right">AED{{ number_format($subtot, 2) }}</td>
                            </tr>

                            <tr>
                                <td class="text-right"><strong>Paid Amount</strong></td>
                            <td class="text-right">AED{{ number_format($paid_amount, 2) }}</td>
                        </tr>
                        <?php if ($invoice->invoice_status != 'draft') { ?>
                            <tr class="text-dark" style="background-color: #d3eae4ff">
                                <td class="text-right">
                                    <strong>Balance Due</strong>
                                </td>
                                <td class="text-right font-weight-bold">
                                        AED{{ number_format($due_bal, 2) }}
                                    </td>
                                </tr>
                            <?php } ?>

                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div style="margin-top: 10rem" class="text-dark">
                    <!-- <h6>Notes</h6> -->
                    <p class="mb-0" style="font-size:16px;">Thank you for your business.</p>
                <hr>

                    <p class="mb-2">DEPOSIT WILL BE RETURNED 30 DAYS AFTER RETURNING THE VEHICLE.</p>
                    <p class="mb-0" style="line-height: 1.5; font-size:13px;">Payment Detail:</p>
                    <p class="mb-0" style="line-height: 1.5; font-size:13px;">Bank Name: WIO</p>
                    <p class="mb-0" style="line-height: 1.5; font-size:13px;">Account Name: Rapid Rentals -FZCO</p>
                    <p class="mb-0" style="line-height: 1.5; font-size:13px;">IBN : AE790860000009637084836</p>
                    <p class="mb-0" style="line-height: 1.5; font-size:13px;">Account No: 9637084836</p>
                    <p class="mb-2" style="line-height: 1.5; font-size:13px;">BIC/SWIFT : WIOBAEADXXX</p>
                    <p class="mb-2">Queries:+971 50 366 1754</p>
                    <p class="mb-0">Complaints & Suggestions: +971 54 508 2661 or Email: idrees@rapidenterprises.ae</p>
                </div>
                {{-- <hr> --}}
            </div>
        </section>
    </div>

@endsection

 @section('script')
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
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

    const statusButtons = document.querySelectorAll('.status-confirm');
    statusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
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

   const printBtn = document.getElementById('printBtn');

    if (printBtn) {
        printBtn.addEventListener('click', function() {
            document.body.classList.add('printing');

            const printHideElements = document.querySelectorAll('.printHide');
            printHideElements.forEach(el => el.style.display = 'none');

            window.print();

            document.body.classList.remove('printing');
            printHideElements.forEach(el => el.style.display = '');
        });
    }
});
</script>
@endsection
