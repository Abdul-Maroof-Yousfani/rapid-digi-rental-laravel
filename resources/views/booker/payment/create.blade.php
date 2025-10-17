@extends('admin.master-main')
@section('content')
    @php
        $userRole = Auth::user()->getRoleNames()->first();
        $bookingId = request()->query('booking_id');
        $formAction = $bookingId ? url($userRole . '/pending-payment/' . $bookingId) : url($userRole . '/payment');
    @endphp
    <style>
        .disableClick {
            cursor: not-allowed !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__arrow,
        .select2-container--default .select2-selection--single .select2-selection__arrow {

            width: 16px !important;

        }

        .table-responsive {
            overflow: scroll;
            white-space: nowrap
        }
    </style>
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card-body">
                            <h3 class="mb-0">Payment Voucher</h3>
                        </div>
                    </div>
                </div>
                <form action="{{ url('payment') }}" method="post" id="payment_form" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4></h4>
                                    <div class="card-header-action"></div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="sortable-table">
                                            <tbody class="ui-sortable">
                                                <tr>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Booking</label><br>
                                                            <select name="booking_id" id="booking_id"
                                                                onchange="bookingChange()"
                                                                class="form-control select2 booking_id">
                                                                <option value="">Select Booking</option>
                                                                @foreach ($bookings as $item)
                                                                    <option value="{{ $item->id }}">
                                                                        {{ $item->bookingInvoice->zoho_invoice_number }} |
                                                                        {{ $item->customer?->customer_name ?? 'No Customer' }}
                                                                    </option>

                                                                @endforeach

                                                            </select><br>
                                                            <input type="hidden" value="" name="payment_id"
                                                                class="payment_id" readonly>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle p-0">
                                                        <div class="form-group">
                                                            <label for="">Booking Amount</label><br>
                                                            <input type="number" value="" name="booking_amount"
                                                                class="form-control booking_amount" readonly>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Customer name</label><br>
                                                            <input type="text" value="" name="customer_name"
                                                                class="form-control customer_name" disabled>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Payment Method</label><br>
                                                            <select name="payment_method"
                                                                class="form-control payment_method select2" required>
                                                                <option value="">Payment method</option>
                                                                @foreach ($paymentMethod as $item)
                                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        {{-- <div class="form-group">
                                                            <label for="">Deposit Amount</label><br>
                                                            <input type="number" value="" name=""
                                                                class="form-control initial_deposit" readonly>
                                                            <input type="hidden" value="" name="deposit_amount"
                                                                class="form-control deposit_amount" readonly>
                                                        </div> --}}

                                                        <div class="form-group">
                                                            <label for="">Bank</label><br>
                                                            <select name="bank_id" class="form-control select2 bank_id"
                                                                disabled>
                                                                <option value="">Select Bank</option>
                                                                @foreach ($bank as $item)
                                                                    <option value="{{ $item->id }}">{{ $item->bank_name }} |
                                                                        {{ $item->account_number }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                    </td>
                                                    <td class="align-middle">
                                                        {{-- <div class="form-group">
                                                            <label for="">Pending Amount</label><br>
                                                            <input type="number" placeholder="" value=""
                                                                name="pending_amount" class="form-control pending_amount"
                                                                readonly>
                                                            <input type="hidden" class="form-control restrict">
                                                        </div> --}}


                                                        <div class="form-group">
                                                            <label for="image">Upload Image</label><br>
                                                            <input type="file" name="image" id="image" class="form-control"
                                                                accept="image/*">
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr style="display: none">
                                                    <td class="align-middle">
                                                        {{-- <div class="form-group">
                                                            <label for="image">Upload Image</label><br>
                                                            <input type="file" name="image" id="image" class="form-control"
                                                                accept="image/*">
                                                        </div> --}}

                                                    </td>
                                                    <td class="align-middle">
                                                    </td>
                                                    <td class="align-middle">
                                                        {{-- <div class="form-group">
                                                            <label for="">Remaining Deposit</label><br>
                                                            <input type="number" placeholder="" value="" name=""
                                                                class="form-control remaining_deposit" disabled>
                                                        </div> --}}
                                                    </td>
                                                </tr>
                                                <tr style="display: none">
                                                    {{-- <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Receive Amount</label><br>
                                                            <input type="number" placeholder="Receive Amount" value="0"
                                                                name="" class="form-control amount_receive" min="0"
                                                                step="0.01" required>
                                                        </div>
                                                    </td> --}}
                                                    <td class="align-middle already_paid">

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Invoices List</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>Invoice No.</th>
                                                    <th>Salik Qty | Amount</th>
                                                    <th>Fine Qty | Amount</th>
                                                    <th>Renew</th>
                                                    <th>Rent</th>
                                                    <th>Status</th>
                                                    <th>Total Amount</th>
                                                    <th style="display: none">Add Deposit</th>
                                                    <th>Receive Amount</th>
                                                </tr>
                                            </thead>
                                            <!-- <tbody class="ui-sortable" id="booking_detail"></tbody> -->

                                            <tbody class="ui-sortable" id="booking_detail"></tbody>
                                            <tr>
                                                <td colspan="8" class="text-center font-weight-bold ">Payment Management
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">
                                                    <span data-toggle="tooltip"
                                                        title="The initial deposit amount available">Deposit Amount</span>
                                                </td>
                                                <td>
                                                    <input type="number" name="initial_deposit"
                                                        class="form-control initial_deposit" placeholder="0.00" readonly>
                                                    <input type="hidden" class="form-control deposit_amount" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">
                                                    <!-- <div class="form-check"> -->
                                                    <input type="hidden" name="used_deposit_amount" value="0">
                                                    <input type="checkbox" id="used_deposit_amount"
                                                        name="used_deposit_amount" value="1" onchange="usedDeposit()"
                                                        disabled>
                                                    <!-- </div> -->
                                                    Use Deposit Amount
                                                    <small class="form-text text-muted">Check to apply deposit (enabled when
                                                        deposit > 0).</small>
                                                </td>
                                                <td>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">Receive Amount <span
                                                        class="text-danger">*</span></td>
                                                <td>
                                                    <input type="number" value="" name="amount_receive"
                                                        class="form-control amount_receive" step="0.01">

                                                    <small class="form-text text-danger d-none" id="amount-error">Amount
                                                        cannot exceed pending amount.</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">Pending Amount</td>
                                                <td>
                                                    <input type="number" class="form-control pending_amount"
                                                        placeholder="0.00" readonly>
                                                    <input type="hidden" class="form-control restrict">
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="7" class="text-right align-middle">Remaining Deposit</td>
                                                <td>
                                                    <input type="number" class="form-control remaining_deposit"
                                                        placeholder="0.00" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">
                                                    <!-- <div class="form-check"> -->
                                                    <input type="hidden" name="adjust_invoice" value="0">
                                                    <input type="checkbox" id="adjust_invoice" name="adjust_invoice"
                                                        value="1" onchange="usedDepositAgainstInvoice()" disabled>
                                                    <!-- </div> -->
                                                    Adjust Deposit Against Invoice
                                                    <small class="form-text text-muted">Check to link deposit to a specific
                                                        invoice.</small>
                                                </td>
                                                <td>

                                                </td>
                                            </tr>
                                            <tr id="invoice_adjustment_section1" style="display: none;">
                                                <td colspan="7" class="text-right align-middle">Reference Invoice No.</td>
                                                <td>
                                                    <input type="text" class="form-control reference_invoice_number"
                                                        name="reference_invoice_number[]"
                                                        placeholder="Enter invoice number">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="7" class="text-right align-middle">Remarks (Optional)</td>
                                                <td>
                                                    <textarea name="remarks[]" class="form-control"
                                                        placeholder="e.g., Payment for invoice #123, special instructions"
                                                        rows="4" style="resize: vertical;"></textarea>
                                                </td>
                                            </tr>
                                            <!-- </tbody> -->





                                        </table>
                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5>Transaction Summary</h5>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Total Pending Amount:</strong> <span
                                                        class="pending_amount">0.00</span></p>
                                                <!-- <p><strong>Deposit Used:</strong> <span id="summary-deposit">0.00</span></p> -->
                                                <p><strong>Amount Received:</strong> <span id="summary-received">0.00</span>
                                                </p>
                                                <p><strong>Remaining Balance:</strong> <span
                                                        id="summary-remaining">0.00</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Add Payment" name="submit" id="submitBtn" class="btn btn-primary">
                        </div>
                    </div>

                </form>

            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $('#payment_form').on('submit', function () {
                $('#submitBtn').prop('disabled', true).val('Processing...');
            });
        });
    </script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('
                error ') }}',
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('
                success ') }}',
            });
        </script>
    @endif

@endsection