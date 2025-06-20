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
                            <h3 class="mb-0">Credit Note</h3>
                        </div>
                    </div>
                </div>
                <form action="{{ url('booker/credit-note') }}" method="post">
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
                                                            <label for="">Booking <span class="text-danger">*</span></label><br>
                                                            <select name="booking_id" id="booking_id" class="form-control select2 booking_id" required>
                                                                <option value="">Select Booking</option>
                                                                @foreach ($filterBooking as $item)
                                                                    <option value="{{ $item->id }}">
                                                                        {{ $item->agreement_no }} | {{ $item->customer->customer_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle p-0">
                                                        <div class="form-group">
                                                            <label for="">Refund Method <span class="text-danger">*</span></label><br>
                                                            <select name="refund_method" class="form-control refund_method select2" required>
                                                                <option value="">Refund method</option>
                                                                @foreach ($refundMethod as $item)
                                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="" class="bankLabel">Bank</label><br>
                                                            <select name="bank_id" class="form-control select2 bank_id" disabled>
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
                                                            <label for="">Refund Amount  <span class="text-danger">*</span></label><br>
                                                            <input type="number" placeholder="Refund Amount" value="" name="refund_amount" class="form-control refund_amount" min="0" step="0.01" required readonly>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Refund Date  <span class="text-danger">*</span></label><br>
                                                            <input type="date" value="{{ \Carbon\Carbon::now()->toDateString() }}" name="refund_date" class="form-control refund_date" required>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="form-group">
                                                            <label for="">Remaining Deposit</label><br>
                                                            <input type="number" value="" name="remaining_deposit" class="form-control remaining_deposit"  readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="">Initial Deposit</label><br>
                                                        <input type="number" value="" name="" class="form-control deposit_amount"  disabled>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 d-none">
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
                                                <th>Receive Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody class="ui-sortable" id="booking_detail"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Remarks (Optional)</label>
                                <textarea name="remarks" cols="30" class="form-control" rows="10"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <input type="submit" value="Submit Credit Note" name="submit" class="btn btn-primary">
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
        $(document).ready(function(){
            $(document).on('change', '.refund_method', function(){
                var refundMethod= $(this).val();
                if(refundMethod==3){
                    $('.bank_id').removeAttr('disabled');
                    $('.bankLabel').append(' <span class="text-danger">*</span>');
                }
                else {
                    $('.bank_id').attr('disabled', true).val('');
                    $('.bankLabel .text-danger').remove();
                }
            });

            $(document).on('change', '.booking_id', function(){
                $('.amount_receive').removeAttr('disabled');
                var bookingID= $(this).val();
                $.ajax({
                    url : '/get-booking-detail/'+bookingID,
                    type: 'GET',
                    success:function(response){
                    $('#booking_detail').html('');
                        if(response){
                            remainingDeposit= response.deposit_amount - response.deduct_amount;
                            $('.booking_amount').val(response.booking_amount);
                            $('.deposit_amount').val(response.deposit_amount);
                            $('.refund_amount').val(remainingDeposit);
                            $('.remaining_deposit').val(remainingDeposit);
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
                                    '<td class="recieve_amount">'+
                                        '<input type="hidden" name="invoice_id[]" value="'+invoice.invoice_id+'">'+
                                        '<input type="hidden" name="invoice_amount[]" value="'+invoice.invoice_amount+'">'+
                                        '<input type="text" name="invPaidAmount[]" value="0" class="form-control invPaidAmount" readonly>'+
                                    '</td></tr>';
                                $('#booking_detail').append(row);
                            });
                        }
                    }
                });
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
            });
        });
    </script>
@endsection
