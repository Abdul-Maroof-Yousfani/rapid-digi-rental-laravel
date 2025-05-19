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
                                                    <select name="bank" class="form-control select2 bank" disabled>
                                                        <option value="">Select Bank</option>
                                                        @foreach ($bank as $item)
                                                            <option value="{{ $item->id }}">{{ $item->bank_name }} | {{ $item->account_number }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="align-middle">
                                                <div class="form-group">
                                                    <label for="">Customer name</label><br>
                                                    <input type="text" value="" name="" class="form-control customer_name">
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="form-group">
                                                    <label for="">Booking Amount</label><br>
                                                    <input type="number" value="" name="booking_amount" class="form-control booking_amount" disabled>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="form-group">
                                                    <label for="">Deposit Amount</label><br>
                                                    <input type="number" value="" name="deposit_amount" class="form-control deposit_amount" disabled>
                                                    <input type="hidden" name="deposit_id" value="" class="deposit_id">
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="align-middle">
                                                <div class="form-group">
                                                    <label for="">Receive Amount</label><br>
                                                    <input type="number" placeholder="Receive Amount" value="" name="" class="form-control amount_receive" disabled>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="form-group">
                                                    <label for="">Remaining Deposit</label><br>
                                                    <input type="number" placeholder="" value="" name="" class="form-control remaining_deposit" disabled>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                            </td>
                                        </tr>


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br><br>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Invoices List</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    {{-- <table class="table table-striped" id="sortable-table">
                                        <tbody class="ui-sortable"> --}}
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
        $(document).ready(function(){
            $(document).on('change', '.payment_method', function(){
                var paymentMethod= $(this).val();
                if(paymentMethod==3){
                    $('.bank').removeAttr('disabled'); }
                else {
                    $('.bank').attr('disabled', true).val('');}
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
                            $('.deposit_id').val(response.deposit_id);
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
                                subtotal += parseFloat(invoice.invoice_amount) || 0;
                                var row = '<tr><td class="text-center pt-2"><div class="custom-checkbox custom-control"><input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-1"><label for="checkbox-1" class="custom-control-label">&nbsp;</label></div></td>' +
                                    '<td>' + invoice.zoho_invoice_number + '</td>' +
                                    '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
                                    '<td>' + invoice.summary.fine_qty + ' | ' + invoice.summary.fine_amount + '</td>' +
                                    '<td>' + invoice.summary.renew_amount + '</td>' +
                                    '<td>' + invoice.summary.rent_amount + '</td>' +
                                    '<td>' + invoice.invoice_status + '</td>' +
                                    '<td>' + invoice.invoice_amount + '</td>' +
                                    '<td class="text-center"><div class="custom-control custom-checkbox">' +
                                        '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '">' +
                                        '<label class="custom-control-label" for="depositCheck' + index + '"></label></div></td>' +
                                    '<td class="recieve_amount"><input type="number" value="" name="" class="form-control invoice_amount" readonly></td>' +
                                    '</tr>';
                                $('#booking_detail').append(row);
                            });
                            $('#booking_detail').append('<tr><td colspan="9" class="text-right">Sub total</td><td><input type="number" value="' + subtotal.toFixed(2) + '" name="" class="form-control " readonly></td></tr>');
                        }
                    }
                });
            });

            $(document).on('input', '.amount_receive', function () {
                let remainingAmount = parseFloat($(this).val()) || 0;
                $('#booking_detail tr').each(function () {
                    let invoiceAmount = parseFloat($(this).find('td').eq(7).text()) || 0;
                    let inputField = $(this).find('.invoice_amount');

                    if (remainingAmount >= invoiceAmount) {
                        inputField.val(invoiceAmount.toFixed(2));
                        remainingAmount -= invoiceAmount;
                    } else if (remainingAmount > 0) {
                        inputField.val(remainingAmount.toFixed(2));
                        remainingAmount = 0;
                    } else {
                        inputField.val('');
                    }
                });
            });

$(document).on('change', '.add_deposit', function () {
    let $row = $(this).closest('tr');
    let deposit = parseFloat($('.deposit_amount').val()) || 0;
    let remainingDepositField = $('.remaining_deposit');
    let remainingDeposit = parseFloat(remainingDepositField.val()) || deposit;

    let $receiveField = $row.find('.invoice_amount');
    let totalAmount = parseFloat($row.find('td').eq(7).text()) || 0;
    let currentReceived = parseFloat($receiveField.val()) || 0;

    // Store original value once when first checked
    if (!$row.data('initial-received')) {
        $row.data('initial-received', currentReceived);
    }

    let initialReceived = parseFloat($row.data('initial-received')) || 0;
    let dueAmount = totalAmount - initialReceived;

    if ($(this).is(':checked')) {
        // Max usable from deposit: dueAmount or remainingDeposit
        let depositUsed = Math.min(dueAmount, remainingDeposit);
        $receiveField.val((initialReceived + depositUsed).toFixed(2));
        remainingDeposit -= depositUsed;
    } else {
        // Restore back to original
        let afterUncheck = initialReceived.toFixed(2);
        let depositPreviouslyUsed = currentReceived - initialReceived;
        $receiveField.val(afterUncheck);
        remainingDeposit += depositPreviouslyUsed;
        $row.removeData('initial-received');
    }

    remainingDepositField.val(remainingDeposit.toFixed(2));
});






        });
    </script>
@endsection
