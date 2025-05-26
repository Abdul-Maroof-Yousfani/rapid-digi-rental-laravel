@extends('admin.master-main')
@section('content')
    <style>
        .disableClick {
            cursor: not-allowed !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__arrow,
        .select2-container--default .select2-selection--single .select2-selection__arrow {

            width: 16px !important;

        }

        .table-responsive{
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
                            <h3 class="mb-0">Payment Recieve Form</h3>
                        </div>
                    </div>
                </div>
                <form action="{{ url('booker/payment') }}" method="post">
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
                                                        <select name="booking_id" id="booking_id" class="form-control select2 booking_id" required>
                                                            <option value="">Select Booking</option>
                                                            @foreach ($booking as $item)
                                                                <option value="{{ $item->id }}">
                                                                    {{ $item->agreement_no }} | {{ $item->customer->customer_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="align-middle p-0">
                                                    <div class="form-group">
                                                        <label for="">Booking Amount</label><br>
                                                        <input type="number" value="" name="booking_amount" class="form-control booking_amount" readonly>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Customer name</label><br>
                                                        <input type="text" value="" name="customer_name" class="form-control customer_name" disabled>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Payment Method</label><br>
                                                        <select name="payment_method" class="form-control payment_method select2" required>
                                                            <option value="">Payment method</option>
                                                            @foreach ($paymentMethod as $item)
                                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Bank</label><br>
                                                        <select name="bank_id" class="form-control select2 bank_id" disabled>
                                                            <option value="">Select Bank</option>
                                                            @foreach ($bank as $item)
                                                                <option value="{{ $item->id }}">{{ $item->bank_name }} | {{ $item->account_number }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Deposit Amount</label><br>
                                                        <input type="number" value="" name="deposit_amount" class="form-control deposit_amount"  disabled>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Receive Amount</label><br>
                                                        <input type="number" placeholder="Receive Amount" value="" name="" class="form-control amount_receive" min="0" step="0.01">
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Pending Amount</label><br>
                                                        <input type="number" placeholder="" value="" name="pending_amount" class="form-control pending_amount" readonly>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-group">
                                                        <label for="">Remaining Deposit</label><br>
                                                        <input type="number" placeholder="" value="" name="" class="form-control remaining_deposit" disabled>
                                                    </div>
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
                                                <th class="text-center pt-3">
                                                    <div class="custom-checkbox custom-checkbox-table custom-control">
                                                        <input type="checkbox" data-checkboxes="mygroup" data-checkbox-role="dad"
                                                        class="custom-control-input" id="checkbox-all">
                                                        <label for="checkbox-all" class="custom-control-label">&nbsp;</label>
                                                    </div>
                                                </th>
                                                <th>Invoice No.</th>
                                                <th>Salik Qty | Amount</th>
                                                <th>Fine Qty | Amount</th>
                                                <th>Renew</th>
                                                <th>Rent</th>
                                                <th>Status</th>
                                                <th>Total Amount</th>
                                                <th>Add Deposit</th>
                                                <th>Receive Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody class="ui-sortable" id="booking_detail"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Vehicle</th>
                                        <th scope="col">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vehicles"> </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Add Payment" name="submit" class="btn btn-primary">
                        </div>
                    </div>

                </form>

            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            });
        </script>
    @endif

@endsection

@section('script')
    <script>
        $(document).on('keypress', '.amount_receive', function (e) {
            if (e.key === '-' || e.which === 45) {
                e.preventDefault();
            }
        });

        $(document).ready(function(){
            $(document).on('change', '.payment_method', function(){
                var paymentMethod= $(this).val();
                if(paymentMethod==3){ $('.bank_id').removeAttr('disabled'); }
                else { $('.bank_id').attr('disabled', true).val('');}
            });

            $(document).on('change', '.booking_id', function(){
                $('.amount_receive').removeAttr('disabled');
                var bookingID= $(this).val();
                $.ajax({
                    url : '/get-booking-detail/'+bookingID,
                    type: 'GET',
                    success:function(response){
                    $('#vehicles').html('');
                    $('#booking_detail').html('');
                        if(response){
                            $('.booking_amount').val(response.booking_amount);
                            $('.deposit_amount').val(response.deposit_amount);
                            $('.customer_name').val(response.customer);
                            $.each(response.vehicle, function(index, vehicles) {
                                var row = '<tr>' +
                                            '<th scope="row">' + (index + 1) + '</th>' +
                                            '<td>' + vehicles.name + '</td>' +
                                            '<td>' + vehicles.type + '</td>' +
                                        '</tr>';
                                $('#vehicles').append(row);
                            });
                            let subtotal = 0;
                            $.each(response.invoice_detail, function(index, invoice){
                                var row = '<tr><td class="text-center pt-2"><div class="custom-checkbox custom-control"><input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-1"><label for="checkbox-1" class="custom-control-label">&nbsp;</label></div></td>' +
                                    '<td>' + invoice.zoho_invoice_number + '</td>' +
                                    '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
                                    '<td>' + invoice.summary.fine_qty + ' | ' + invoice.summary.fine_amount + '</td>' +
                                    '<td>' + invoice.summary.renew_amount + '</td>' +
                                    '<td>' + invoice.summary.rent_amount + '</td>' +
                                    '<td>' + invoice.invoice_status + '</td>' +
                                    '<td class="invoice_total">' + invoice.invoice_amount + '</td>' +
                                    '<td class="text-center">'+
                                        '<div class="custom-control custom-checkbox">' +
                                        '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '"><input type="hidden" class="addDepositAmount form-control" name="addDepositAmount[]" value="">' +
                                        '<label class="custom-control-label" for="depositCheck' + index + '"></label></div>'+
                                    '</td>'+
                                    '<td class="recieve_amount">'+
                                        '<input type="hidden" name="invoice_id[]" value="'+invoice.invoice_id+'">'+
                                        '<input type="hidden" name="invoice_amount[]" value="'+invoice.invoice_amount+'">'+
                                        '<input type="text" name="invPaidAmount[]" value="0" class="form-control invPaidAmount" readonly>'+
                                    '</td></tr>';
                                $('#booking_detail').append(row);
                            });
                            $('#booking_detail').append('<tr><td colspan="9" class="text-right">Sub total</td>'+
                                '<td><input type="number" value="" name="amount_receive" class="form-control insubtot" readonly></td></tr>'+
                                '<tr><td colspan="9" class="text-right">Remaining Amount</td>'+
                                '<td><input type="number" value="" name="" class="form-control remaining_amount" readonly></td></tr>');
                        }
                    }
                });
            });

            function recalculateTotals() {
                let subtotal = 0;
                $('.invPaidAmount').each(function(){
                    subtotal += parseFloat($(this).val()) || 0;
                });
                $('.insubtot').val(subtotal.toFixed(2));
                let bookingTotal = parseFloat($('.booking_amount').val()) || 0;
                let receivedAmount = subtotal;
                let pendingAmount = bookingTotal - receivedAmount;
                $('.pending_amount').val(pendingAmount.toFixed(2));
                let remaining = receivedAmount;
                $('.remaining_amount').val(remaining.toFixed(2));
            }

            $(document).on('input', '.amount_receive', function () {
                if (!$(this).val()) {
                    $('.pending_amount').val('');
                    $('.remaining_amount').val('');
                    $('.insubtot').val('');
                    $('.invPaidAmount').val(0);
                    $('#booking_detail tr').each(function () {
                        if ($(this).find('.invoice_total').length > 0) {
                            $(this).css('background-color', '');
                        }
                    });
                    return;
                }
                let remainingAmount = parseFloat($(this).val()) || 0;
                $('#booking_detail tr').each(function () {
                    let invoiceTotalCell = $(this).find('.invoice_total');
                    if (invoiceTotalCell.length === 0) {
                        return;
                    }
                    let invoiceAmount = parseFloat($(this).find('.invoice_total').text()) || 0;
                    let inputField = $(this).find('.invPaidAmount');
                    let depositCheckbox = $(this).find('.add_deposit');
                    if (remainingAmount >= invoiceAmount) {
                        inputField.val(invoiceAmount.toFixed(2));
                        remainingAmount -= invoiceAmount;
                        $(this).css('background-color', '#d4edda');
                        depositCheckbox.prop('disabled', true);
                    } else if (remainingAmount > 0) {
                        inputField.val(remainingAmount.toFixed(2));
                        remainingAmount = 0;
                        $(this).css('background-color', '#fff3cd');
                        depositCheckbox.prop('disabled', false);
                    } else {
                        inputField.val(0);
                        $(this).css('background-color', '#fff3cd');
                        depositCheckbox.prop('disabled', false);
                    }
                });
                recalculateTotals();
                $('.remaining_amount').val(remainingAmount.toFixed(2));


            });

            $(document).on('change', '.add_deposit', function () {
                var row = $(this).closest('tr');
                let $depositInput = $('.deposit_amount');
                let deposit = parseFloat($depositInput.val()) || 0;

                let $invoiceAmountInput = row.find('.invPaidAmount');
                let receiveField = parseFloat($invoiceAmountInput.val()) || 0;
                let invoiceTotal = parseFloat(row.find('.invoice_total').text()) || 0;

                if ($(this).is(':checked')) {
                    let remaining = invoiceTotal - receiveField;
                    let appliedDeposit = deposit >= remaining ? remaining : deposit;
                    let newTotal = receiveField + appliedDeposit;
                    let depositLeft = deposit - appliedDeposit;
                    $invoiceAmountInput.data('applied-deposit', appliedDeposit);
                    $invoiceAmountInput.val(newTotal);
                    $depositInput.val(depositLeft);
                    row.find('.invPaidAmount').val(newTotal);
                    $('.remaining_deposit').val(depositLeft);
                    row.find('.addDepositAmount').val(appliedDeposit.toFixed(2));
                } else {
                    let appliedDeposit = parseFloat($invoiceAmountInput.data('applied-deposit')) || 0;
                    let newTotal = parseFloat($invoiceAmountInput.val()) - appliedDeposit;
                    $invoiceAmountInput.val(newTotal);
                    let currentDeposit = parseFloat($depositInput.val()) || 0;
                    $depositInput.val(currentDeposit + appliedDeposit);
                    $invoiceAmountInput.removeData('applied-deposit');
                    row.find('.invPaidAmount').val(newTotal);
                    $('.remaining_deposit').val((currentDeposit + appliedDeposit));
                    row.find('.addDepositAmount').val('');
                }
                if (parseFloat($invoiceAmountInput.val()) >= invoiceTotal) {
                    row.css('background-color', '#d4edda');
                } else if (parseFloat($invoiceAmountInput.val()) > 0 && parseFloat($invoiceAmountInput.val()) < invoiceTotal) {
                    row.css('background-color', '#fff3cd');
                }

                recalculateTotals();
                $('.remaining_amount').val($('.amount_receive').val());


            });

        });
    </script>
@endsection


{{-- let subtotal = 0;
$('.invPaidAmount').each(function(){ subtotal += parseFloat($(this).val()) || 0; });
$('.insubtot').val(subtotal.toFixed(2));
let subtot= $('.insubtot').val(subtotal.toFixed(2));;
$('.remaining_amount').val(remainingAmount.toFixed(2));
let bookingTotal = parseFloat($('.booking_amount').val()) || 0;
let receivedAmount = parseFloat($('.amount_receive').val()) || 0;
let pendingAmount = bookingTotal - receivedAmount;
$('.pending_amount').val(pendingAmount.toFixed(2)); --}}
