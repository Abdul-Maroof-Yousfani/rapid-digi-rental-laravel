@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first() . ' ' . 'Portal'))
@section('content')

Credit Note View
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

    .cred1 {
        display: flex;
        justify-content: space-between;
    }

    p {
        color: #191d21 !important;
    }

    .bg-white {
        overflow: hidden;
    }

    .box1 {
        margin-top: 6px;
        background: #268ddd;
        text-align: center;
        width: 16%;
        color: #fff;
        transform: rotateZ(-45deg);
        margin-left: -71px;
        font-size: 16px
    }
    
    .box1 p {
    color: #fff !important;
    
}
</style>

<!-- Main Content -->
<div class="main-content">

    <!-- Print Button (outside print area, so not printed) -->
    <div class="text-right mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
    </div>

    <section class="section print-area">

        <div class="container my-5 border p-4 bg-white">
            <div class="box1">
                <p>
                    Open
                </p>
            </div>
            <!-- Header -->
            <div style="margin-bottom: 5rem; margin-top: 3rem;" class="row">

                <div class="col">
                    <a href="{{ route('dashboard') }}"> <img alt="image" style="height: 45%; width:50%;" src="{{ asset('assets/img/logo.png') }}"
                            class="header-logo" /> <span class="logo-name"></span>
                    </a>
                    <h5 class="font-weight-bold mb-0" style="line-height: 1.5; color: #343a40;">Rapid Rentals</h5>
                    <p class="text-dark mb-0" style="line-height: 1.5;">A3, L3, 305-C, IFZA Business Park</p>
                    <p class="text-dark mb-0" style="line-height: 1.5;">Dubai Silicon Oasis</p>
                    <p class="text-dark mb-0" style="line-height: 1.5;">+971 55 452 6880</p>
                    <p class="text-dark mb-0" style="line-height: 1.5;">info@rapidenterprises.ae</p>
                    <p class="text-dark mb-0" style="line-height: 1.5;">www.rapidrentals.ae</p>
                </div>

                <div class="col text-right">
                    <h3 style="color:black; font-size:2.3rem; font-weight:50">CREDIT NOTE</h3>
                    <p class="mb-3 font-weight-bold text-dark"># {{ $creditNote->credit_note_no }}</p>

                    <p class="mb-0 font-weight-bold text-dark"> Credits Remaining
                    </p>
                    <h5 class="font-weight-bold text-dark">AED0.00</h5>
                </div>
            </div>

            <!-- Bill To & Invoice Info -->
            <div class="row mb-3">
                <div class="col align-self-end">
                    <p class="mb-0 text-dark" style="line-height: 1.5;">Bill To</p>
                    @php $customer= $creditNote->booking->customer @endphp
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

                    <p class="mb-0 text-dark">{{ $customer->licence }}</p>
                    <!-- <p class="mb-0 text-dark">{{ $customer->country }}</p> -->
                </div>
                <div class="col text-right align-self-end">
                    <p class="text-dark"><strong style="margin-right: 60px;">Credit Date:</strong> {{ $creditNote->refund_date }}</p>
                    <!-- <p class="text-dark"> <strong style="margin-right: 60px;">Due Date:</strong> </p>
                    <p class="text-dark"><strong style="margin-right: 35px;">Sale Person:</strong> </p>
                    <p class="text-dark"><strong style="margin-right: 140px;">TRN Number:</strong> </p> -->
                </div>
            </div>

            @php $GTotal = 0; @endphp

            <!-- Table -->
            <table class="table align-middle">
                <thead class="thead-light">
                    <tr style="background-color: #212525ff" class="text-white">
                        <th class="text-white text-left">#</th>
                        <th class="text-white text-left">Item & Description</th>
                        <th class="text-white text-right">Qty</th>
                        <th class="text-white text-right">Total</th>
                        <th class="text-white text-right">Tax</th>
                        <th class="text-white text-right">Tax Amount</th>
                        <th class="text-white text-right">Net Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 0; @endphp
                    @php $booking = $creditNote->booking; @endphp

                    @if($booking)
                    @foreach ($booking->bookingData as $item)
                    @php $counter++; @endphp
                    <tr style="border-bottom: 2px solid #ADADAD;">
                        <td class="text-left">{{ $counter }}</td>
                        <td class="text-left">
                            {{ $item->vehicle->vehicle_name ?? $item->vehicle->temp_vehicle_detail ?? $item->invoice_type->name ?? '' }} <br>
                            <small>{{ $item->description }}</small>
                        </td>
                        <td class="text-right">{{ $item->quantity ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }}</td>
                        <td class="text-right">{{ $item->tax_name ?? '-'}}</td>
                        <td class="text-right">
                            @php
                            $subTotal = $item->price * $item->quantity;
                            $taxAmount = ($subTotal * $item->tax_percent) / 100;
                            $GTotal += $item->item_total;
                            @endphp
                            {{ number_format($taxAmount, 2) }}
                        </td>
                        <td class="text-right">{{ number_format($item->item_total, 2) }}</td>
                    </tr>
                    @endforeach
                    @endif

                </tbody>
            </table>
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <td class="text-right">Total</td>
                            <td class="text-right">AED{{ $GTotal }}</td>
                        </tr>

                        <tr>
                            <td class="text-right">Credits Used</td>
                            <td class="text-right">AED{{ $GTotal }}</td>
                        </tr>
                        <tr class="text-dark" style="background-color: #ebf0f0ff">
                            <td class="text-right">
                                Credits Remaining
                            </td>
                            <td class="text-right font-weight-bold">
                                AED0.00
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
            <!-- Totals -->
            <!-- <div class="row justify-content-end">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <table class="table">
                        <tr>
                            <th class="text-right">Issued by, signature</th>
                        </tr>
                    </table>
                </div>
            </div> -->

            <!-- Notes -->
            <div style="margin-top: 10rem" class="text-dark">
                <div class="nots">
                    <h6 class="mb-2">Notes</h6>
                    <p class="mb-0">Deposit Amount: {{ $creditNote->remaining_deposit }}AED</p>
                    <p class="mb-0">RELEASE DEPOSIT AMOUNT: {{ $creditNote->remaining_deposit }}AED</p>
                </div>
                <hr>

            </div>

        </div>
    </section>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function printInvoice() {
        window.print();
    }
</script>
@endsection