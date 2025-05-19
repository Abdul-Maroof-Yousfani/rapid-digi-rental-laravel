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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Payment Recieve Form</h4>
                                <div class="card-header-action"></div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="sortable-table">
                                        <tbody class="ui-sortable">
                                        <tr>
                                            <td class="align-middle">
                                                <select name="booking_id" id="booking_id" class="form-control select2 booking_id" required>
                                                    <option value="">Select Booking</option>
                                                    @foreach ($booking as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->agreement_no }} | {{ $item->customer->customer_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="align-middle">
                                                <select name="payment_method" class="form-control payment_method select2" required>
                                                    <option value="">Payment method</option>
                                                    @foreach ($paymentMethod as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="align-middle">
                                                <select name="bank" class="form-control select2 bank" disabled>
                                                    <option value="">Select Bank</option>
                                                    @foreach ($bank as $item)
                                                        <option value="{{ $item->id }}">{{ $item->bank_name }} | {{ $item->account_number }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="align-middle">
                                                <input type="text" value="" placeholder="Customer" name="" class="form-control customer_name">
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" value="" placeholder="Booking Amount" name="booking_amount" class="form-control booking_amount" disabled>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" value="" placeholder="Deposit Amount" name="deposit_amount" class="form-control deposit_amount" disabled>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="align-middle">
                                                <input type="number" placeholder="Receive Amount" value="" name="" class="form-control amount_receive" disabled>
                                            </td>
                                            <td class="align-middle">
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
                                    '<td class="recieve_amount"><input type="number" value="" name="" class="form-control invoice_amount" readonly></td>' +
                                    '</tr>';
                                $('#booking_detail').append(row);
                            });
                            $('#booking_detail').append('<tr><td colspan="8" class="text-right">Sub total</td><td><input type="number" value="' + subtotal.toFixed(2) + '" name="" class="form-control " readonly></td></tr>');
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

        });
    </script>
@endsection
