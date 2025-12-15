function recalculateTotals() {
    let useDeposit = $('#used_deposit_amount').is(':checked');
    let bookingAmount = parseFloat($('.booking_amount').val()) || 0;
    let deposit = parseFloat($('.initial_deposit').val()) || 0;

    let subtotal = 0;
    $('.invPaidAmount').each(function () {
        subtotal += parseFloat($(this).val()) || 0;
    });
    $('.insubtot').val(subtotal.toFixed(2));

    let totalReceived = subtotal;
    let pending = bookingAmount - totalReceived;

    if (useDeposit) {
        let depositToUse = Math.min(deposit, pending);
        pending -= depositToUse;
        let remaining = deposit - depositToUse;
        $('.remaining_deposit').val(remaining.toFixed(2));
    } else {
        $('.remaining_deposit').val(deposit.toFixed(2));
    }

    $('.pending_amount').val(pending.toFixed(2));
    $('.remaining_amount').val(totalReceived.toFixed(2));
    updateTransactionSummary();
}





$(document).ready(function () {

    // Country Dropdown Search code for all application Modals
    $('select.select2').select2();
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('select.select2').select2({
            dropdownParent: $(this)
        });
    });


    $('#booking_id').trigger('change');

    // (-) Not Enter in any Number Input Fields
    $(document).on('keypress', '.amount_receive', function (e) {
        if (e.key === '-' || e.which === 45) {
            e.preventDefault();
        }
    });

    // Render invoices and vehicles against booking in payment form
    $(document).on('change', '.payment_method', function () {
        var paymentMethod = $(this).val();
        if (paymentMethod == 3 || paymentMethod == 4 || paymentMethod == 5) { $('.bank_id').removeAttr('disabled'); }
        else { $('.bank_id').attr('disabled', true).val(''); }
    });


    $(document).on('input', '.amount_receive', function () {
        let useDeposit = $('#used_deposit_amount').is(':checked');
        let enteredValue = parseFloat($(this).val()) || 0;
        let pending = parseFloat($('.pending_amount').val()) || 0;

        // Cap entry to pending only
        if (enteredValue > pending) {
            $(this).val(pending.toFixed(2));
            enteredValue = pending;
        }

        recalculateTotals();

        // let remainingAmount = parseFloat($(this).val()) || 0;
        // let depositAmount = parseFloat($('.deposit_amount').val()) || 0;
        // $('#booking_detail tr').each(function () {
        //     let invoiceTotalCell = $(this).find('.invoice_total');
        //     if (invoiceTotalCell.length === 0) return;

        //     // let invoiceAmount = parseFloat($(this).find('.invoice_total').text()) || 0;
        //     let invoiceTotal = parseFloat(invoiceTotalCell.text()) || 0;
        //     let inputField = $(this).find('.invPaidAmount');
        //     let depositCheckbox = $(this).find('.add_deposit');
        //     // let originalPaid = parseFloat(inputField.attr('data-original')) || 0;
        //     let originalPaid = (parseFloat(inputField.attr('data-original')) || 0) + (parseFloat(inputField.data('applied-deposit')) || 0);


        //     // Already fully paid — skip
        //     if (originalPaid.toFixed(2) === invoiceTotal.toFixed(2)) {
        //         inputField.val(originalPaid.toFixed(2));
        //         $(this).css('background-color', '#d4edda');
        //         depositCheckbox.prop('disabled', true);
        //         return;
        //     }

        //     let remainingForThis = invoiceTotal - originalPaid;
        //     if (remainingAmount >= remainingForThis) {
        //         inputField.val((originalPaid + remainingForThis).toFixed(2));
        //         remainingAmount -= remainingForThis;
        //         $(this).css('background-color', '#d4edda');
        //         depositCheckbox.prop('disabled', true);
        //     } else if (remainingAmount > 0) {
        //         inputField.val((originalPaid + remainingAmount).toFixed(2));
        //         remainingAmount = 0;
        //         $(this).css('background-color', '#fff3cd');
        //         depositCheckbox.prop('disabled', false);
        //     } else {
        //         inputField.val(originalPaid.toFixed(2));
        //         $(this).css('background-color', '#fff3cd');
        //         depositCheckbox.prop('disabled', false);
        //     }
        // });
        // recalculateTotals();
        // $('.remaining_amount').val(remainingAmount.toFixed(2));


    });

    $(document).on('change', '.add_deposit', function () {
        // var row = $(this).closest('tr');
        // let $depositInput = $('.deposit_amount');
        // let deposit = parseFloat($depositInput.val()) || 0;

        // let $invoiceAmountInput = row.find('.invPaidAmount');
        // let receiveField = parseFloat($invoiceAmountInput.val()) || 0;
        // let invoiceTotal = parseFloat(row.find('.invoice_total').text()) || 0;

        // if ($(this).is(':checked')) {
        //     let remaining = invoiceTotal - receiveField;
        //     let appliedDeposit = deposit >= remaining ? remaining : deposit;
        //     let newTotal = receiveField + appliedDeposit;
        //     let depositLeft = deposit - appliedDeposit;
        //     $invoiceAmountInput.data('applied-deposit', appliedDeposit);
        //     $invoiceAmountInput.val(newTotal);
        //     $depositInput.val(depositLeft);
        //     row.find('.invPaidAmount').val(newTotal);
        //     $('.remaining_deposit').val(depositLeft);
        //     row.find('.addDepositAmount').val(appliedDeposit.toFixed(2));
        // } else {
        //     let appliedDeposit = parseFloat($invoiceAmountInput.data('applied-deposit')) || 0;
        //     let newTotal = parseFloat($invoiceAmountInput.val()) - appliedDeposit;
        //     $invoiceAmountInput.val(newTotal);
        //     let currentDeposit = parseFloat($depositInput.val()) || 0;
        //     $depositInput.val(currentDeposit + appliedDeposit);
        //     $invoiceAmountInput.removeData('applied-deposit');
        //     row.find('.invPaidAmount').val(newTotal);
        //     $('.remaining_deposit').val((currentDeposit + appliedDeposit));
        //     row.find('.addDepositAmount').val('');
        // }
        // if (parseFloat($invoiceAmountInput.val()) >= invoiceTotal) {
        //     row.css('background-color', '#d4edda');
        // } else if (parseFloat($invoiceAmountInput.val()) > 0 && parseFloat($invoiceAmountInput.val()) < invoiceTotal) {
        //     row.css('background-color', '#fff3cd');
        // }

        // recalculateTotals();
        // $('.remaining_amount').val($('.amount_receive').val());


    });

});





function bookingChange() {
    var bookingID = $('#booking_id').val();

    if (bookingID) {
        $('.amount_receive').removeAttr('disabled');
        $('#used_deposit_amount').removeAttr('disabled');
        $('#adjust_invoice').removeAttr('disabled');
    } else {
        $('.amount_receive').attr('disabled', true);
        $('#used_deposit_amount').attr('disabled', true);
        $('#adjust_invoice').attr('disabled', true);
        return;
    }

    $.ajax({
        url: '/get-booking-detail/' + bookingID,
        type: 'GET',
        success: function (response) {
            $('#vehicles').html('');
            $('#booking_detail').html('');

            if (response) {
                let bookingAmount = parseFloat(response.booking_amount) || 0;
                let depositAmount = parseFloat(response.deposit_amount) || 0;
                let remainingDeposit = parseFloat(response.remaining_deposit) || 0;
                let alreadyPaid = parseFloat(response.paid_amount) || 0;

                $('.booking_amount').val(bookingAmount);
                $('.initial_deposit').val(depositAmount);
                $('.deposit_amount').val(remainingDeposit);
                $('.remaining_deposit').val(remainingDeposit);
                $('.customer_name').val(response.customer);

                let pending = bookingAmount - alreadyPaid;
                $('.pending_amount').val(pending.toFixed(2));
                $('.restrict').val(pending.toFixed(2));

                // Reset receive amount
                $('.amount_receive').val('');
                $('.insubtot').val('');
                $('.invPaidAmount').val(0);

                $('.payment_id').val(response.payment_id);

                if (response.paid_amount > 0) {
                    $('.already_paid').html(
                        '<div style="display: none;" class="form-group">' +
                        '<label for="">Already Paid</label><br>' +
                        '<input type="number" placeholder="" value="' + response.paid_amount + '" name="" class="form-control" min="0" step="0.01">' +
                        '</div>'
                    );
                } else {
                    $('.already_paid').html('');
                }

                $.each(response.vehicle, function (index, vehicles) {
                    var row = '<tr>' +
                        '<th scope="row">' + (index + 1) + '</th>' +
                        '<td>' + vehicles.name + '</td>' +
                        '<td>' + vehicles.type + '</td>' +
                        '</tr>';
                    $('#vehicles').append(row);
                });

                let subtotal = 0;
                $.each(response.invoice_detail, function (index, invoice) {
                    var paid = parseFloat(invoice.paid_amount) || 0;
                    var total = parseFloat(invoice.invoice_amount) || 0;
                    var deposit = parseFloat(invoice.deposit_amount) || 0;
                    var remainingDeposit = parseFloat(response.remaining_deposit) || 0;
                    var paymentDataID = invoice.payment_data_id;
                    var nonRefundable = parseFloat(response.non_refundable_amount) || 0;
                    var disableCheckbox = (remainingDeposit === 0 || paid.toFixed(2) === total.toFixed(2));

                    var rowColor = '';
                    if (paid > 0) {
                        rowColor = (paid.toFixed(2) === total.toFixed(2)) ? 'background-color:#d4edda;' : 'background-color:#fff3cd;';
                    }
                    let displayTotal = total + (nonRefundable > 0 ? nonRefundable : 0);
                    let nonRefundableText = (nonRefundable > 0)
                        ? ' <small class="text-info d-block">(Incl. Non-Refundable)</small>'
                        : '';
                    var row = '<tr style="' + rowColor + '" data-invoice-id="' + invoice.invoice_id + '">' +
                        '<td class="text-center">' +
                        '<input type="checkbox" class="invoice-checkbox" name="selected_invoices[]" value="' + invoice.invoice_id + '" checked>' +
                        '</td>' +
                        '<td><input type="hidden" name="paymentData_id[]" value="' + paymentDataID + '">' + invoice.zoho_invoice_number + '</td>' +
                        '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
                        '<td>' + invoice.summary.park_amount + '</td>' +
                        '<td>' + invoice.summary.fine_amount + '</td>' +
                        '<td>' + invoice.summary.renew_amount + '</td>' +
                        '<td>' + (invoice.summary.rent_amount).toFixed(2) + '</td>' +
                        '<td>' + invoice.invoice_status + '</td>' +
                        '<td class="invoice_total">' + total.toFixed(2) + '</td>' +
                        // '<td class="invoice_total">' + displayTotal.toFixed(2) + nonRefundableText + '</td>' +

                        '<td class="text-center" style="display: none;">' +
                        '<div class="custom-control custom-checkbox">' +
                        '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '"' + (disableCheckbox ? ' disabled' : '') + '>' +
                        '<input type="hidden" class="addDepositAmount form-control" name="addDepositAmount[]" value="0">' +
                        '<label class="custom-control-label" for="depositCheck' + index + '"></label></div>' +
                        '</td>' +
                        '<td class="recieve_amount">' +
                        '<input type="hidden" name="invoice_id[]" value="' + invoice.invoice_id + '">' +
                        '<input type="hidden" name="invoice_amount[]" value="' + total + '">' +
                        '<input type="text" name="invPaidAmount[]" value="' + paid.toFixed(2) + '" data-original="' + paid.toFixed(2) + '" class="form-control invPaidAmount" readonly>' +
                        '</td></tr>';
                    $('#booking_detail').append(row);
                });

                $('#booking_detail').append(
                    '<tr style="display: none"><td colspan="7" class="text-right">Remaining Amount</td>' +
                    '<td><input type="number" value="" name="" class="form-control remaining_amount" readonly></td></tr>');

                // ===================== Auto Apply Deposit START =====================
                let autoDeposit = parseFloat(response.remaining_deposit) || 0;
                $('#booking_detail tr').each(function () {
                    let row = $(this);
                    let invoiceTotalCell = row.find('.invoice_total');
                    if (invoiceTotalCell.length === 0) return;

                    let invoiceTotal = parseFloat(invoiceTotalCell.text()) || 0;
                    let inputField = row.find('.invPaidAmount');
                    let depositCheckbox = row.find('.add_deposit');
                    let depositInputHidden = row.find('.addDepositAmount');

                    let alreadyPaid = parseFloat(inputField.val()) || 0;
                    let remainingInvoice = invoiceTotal - alreadyPaid;

                    if (autoDeposit <= 0 || remainingInvoice <= 0) return;

                    let applyAmount = (autoDeposit >= remainingInvoice) ? remainingInvoice : autoDeposit;

                    // Apply deposit
                    // let newPaidAmount = alreadyPaid + applyAmount;
                    let newPaidAmount = alreadyPaid;
                    inputField.val(newPaidAmount.toFixed(2));

                    // inputField.val(newPaidAmount.toFixed(2)).trigger('change');

                    depositInputHidden.val(applyAmount.toFixed(2));
                    depositCheckbox.prop('checked', true);
                    inputField.data('applied-deposit', applyAmount);

                    // Background coloring
                    if (newPaidAmount.toFixed(2) === invoiceTotal.toFixed(2)) {
                        row.css('background-color', '#d4edda');
                    } else {
                        row.css('background-color', '#fff3cd');
                    }

                    autoDeposit -= applyAmount;
                });

                $('.deposit_amount').val(autoDeposit.toFixed(2));
                $('.remaining_deposit').val(autoDeposit.toFixed(2));
                // ===================== Auto Apply Deposit END =====================
                setTimeout(function () {
                    recalculateTotals();
                }, 100);
                updateTransactionSummary();
            }
        }
    });
}
function updateTransactionSummary() {
    let totalInvoiceAmount = parseFloat($('.booking_amount').val()) || 0;
    let depositUsed = 0;
    let amountReceived = parseFloat($('.amount_receive').val()) || 0;
    let useDeposit = $('#used_deposit_amount').is(':checked');

    if (useDeposit) {
        depositUsed = parseFloat($('.initial_deposit').val()) || 0;
    } else {
        $('.invPaidAmount').each(function () {
            depositUsed += parseFloat($(this).val()) || 0;
        });
    }

    let pendingAmount = totalInvoiceAmount - depositUsed - amountReceived;
    pendingAmount = Math.max(0, pendingAmount); // prevent negative

    // Update the summary spans
    $('.pending_amount').text(pendingAmount.toFixed(2));
    $('#summary-deposit').text(depositUsed.toFixed(2));
    $('#summary-received').text(amountReceived.toFixed(2));
    $('#summary-remaining').text(pendingAmount.toFixed(2));
}



function usedDeposit() {
    let useDeposit = $('#used_deposit_amount').is(':checked');
    let deposit = parseFloat($('.initial_deposit').val()) || 0;
    let pending = parseFloat($('.restrict').val()) || 0;

    if (useDeposit) {
        let deposit = parseFloat($('.initial_deposit').val()) || 0;
        let pending = parseFloat($('.restrict').val()) || 0;

        let depositToUse = Math.min(deposit, pending); // e.g. 1000
        let newPending = pending - depositToUse;       // e.g. 100
        let newRemaining = deposit - depositToUse;     // e.g. 0

        $('.pending_amount').val(newPending.toFixed(2));
        $('.remaining_deposit').val(newRemaining.toFixed(2));

        if (newPending > 0) {
            $('.amount_receive').prop('readonly', false); // Allow input
        } else {
            $('.amount_receive').val(0).prop('readonly', true); // Disable only if nothing to receive
        }


    }


    recalculateTotals();
}



function usedDepositAgainstInvoice() {
    let adjustInvoice = $('#adjust_invoice').is(':checked');
    let deposit = parseFloat($('.initial_deposit').val()) || 0;
    let invoice_amount = parseFloat($('.invoice_amount').val()) || 0;


    let remainingDepositBefore = parseFloat($('.remaining_deposit').val()) || 0;

    // Invoice total can come from server or fixed
    let invoiceAmount = parseFloat($('.insubtot').val()) || 2100;

    if (adjustInvoice && remainingDepositBefore === 0) {
        $('#adjust_invoice').prop('checked', false);
        alert("Deposit amount already used");
        return;
    }

    if (adjustInvoice) {
        $('#used_deposit_amount').prop('checked', false).prop('disabled', true);
        $('#invoice_adjustment_section1').show();

        // ✅ Only apply deposit if remaining exists
        let alreadyUsedDeposit = deposit - remainingDepositBefore;
        let remainingDeposit = deposit - alreadyUsedDeposit;
        let depositUsed = Math.min(remainingDeposit, invoiceAmount);
        let pending = invoiceAmount;
        let newRemainingDeposit = deposit - (alreadyUsedDeposit + depositUsed);

        // Update UI
        $('.invPaidAmount').val(invoice_amount.toFixed(2));
        $('.pending_amount').val(pending.toFixed(2));
        $('.amount_receive').val(pending.toFixed(2));
        $('.remaining_deposit').val(newRemainingDeposit.toFixed(2));
    } else {
        $('#used_deposit_amount').prop('disabled', false);
        $('#invoice_adjustment_section1').hide();
        $('.reference_invoice_number').val('');
        $('.invPaidAmount').val('');
        $('.insubtot').val('');
        $('.pending_amount').val('');
        $('.amount_receive').val('');
    }
    recalculateTotals();

}







function bookingChangessss() {
    $('.amount_receive').removeAttr('disabled');
    var bookingID = $('#booking_id').val();
    $.ajax({
        url: '/get-booking-detail/' + bookingID,
        type: 'GET',
        success: function (response) {
            $('#vehicles').html('');
            $('#booking_detail').html('');
            if (response) {
                $('.booking_amount').val(response.booking_amount);
                $('.initial_deposit').val(response.deposit_amount);
                $('.deposit_amount').val(response.remaining_deposit);
                $('.remaining_deposit').val(response.remaining_deposit);
                $('.customer_name').val(response.customer);
                $('.pending_amount').val(parseFloat(response.remaining_amount).toFixed(2));
                $('.restrict').val(parseFloat(response.remaining_amount).toFixed(2));

                // Get Payment ID
                $('.payment_id').val(response.payment_id);

                if (response.paid_amount > 0) {
                    $('.already_paid').html('');
                    $('.already_paid').append(
                        '<div class="form-group">' +
                        '<label for="">Already Paid</label><br>' +
                        '<input type="number" placeholder="" value="' + response.paid_amount + '" name="" class="form-control" min="0" step="0.01">' +
                        '</div>');
                } else {
                    $('.already_paid').html('');
                }

                let subtotal = 0;
                $.each(response.invoice_detail, function (index, invoice) {
                    var paid = parseFloat(invoice.paid_amount) || 0;
                    var total = parseFloat(invoice.invoice_amount) || 0;
                    var deposit = parseFloat(invoice.deposit_amount) || 0;
                    var remainingDeposit = parseFloat(response.remaining_deposit) || 0;

                    // Get PaymentData ID
                    var paymentDataID = invoice.payment_data_id;

                    // var disableCheckbox = (deposit === 0 || paid.toFixed(2) === total.toFixed(2));
                    var disableCheckbox = (remainingDeposit === 0 || paid.toFixed(2) === total.toFixed(2));


                    // Row background color logic
                    var rowColor = '';
                    if (paid > 0) {
                        if (paid.toFixed(2) === total.toFixed(2)) {
                            rowColor = 'background-color:#d4edda;'; // green
                        } else {
                            rowColor = 'background-color:#fff3cd;'; // yellow
                        }
                    }

                    var row = '<tr style="' + rowColor + '">' +
                        '<td><input type="hidden" name="paymentData_id[]" value="' + paymentDataID + '">' + invoice.zoho_invoice_number + '</td>' +
                        '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
                        '<td>' + invoice.summary.fine_qty + ' | ' + invoice.summary.fine_amount + '</td>' +
                        '<td>' + invoice.summary.renew_amount + '</td>' +
                        '<td>' + (invoice.summary.rent_amount).toFixed(2) + '</td>' +
                        '<td>' + invoice.invoice_status + '</td>' +
                        '<td class="invoice_total">' + invoice.invoice_amount + '</td>' +
                        '<td class="text-center">' +
                        '<div class="custom-control custom-checkbox">' +
                        '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '"' + (disableCheckbox ? ' disabled' : '') + '><input type="hidden" class="addDepositAmount form-control" name="addDepositAmount[]" value="' + deposit + '">' +
                        '<label class="custom-control-label" for="depositCheck' + index + '"></label></div>' +
                        '</td>' +
                        '<td class="recieve_amount">' +
                        '<input type="hidden" name="invoice_id[]" value="' + invoice.invoice_id + '">' +
                        '<input type="hidden" name="invoice_amount[]" value="' + invoice.invoice_amount + '">' +
                        '<input type="text" name="invPaidAmount[]" value="' + paid.toFixed(2) + '" data-original="' + paid.toFixed(2) + '" class="form-control invPaidAmount" readonly>' +
                        '</td></tr>';
                    $('#booking_detail').append(row);
                });
                $('#booking_detail').append('<tr><td colspan="8" class="text-right">Sub total</td>' +
                    '<td><input type="number" value="" name="amount_receive" class="form-control insubtot" readonly></td></tr>' +
                    '<tr style="display: none"><td colspan="8" class="text-right">Remaining Amount</td>' +
                    '<td><input type="number" value="" name="" class="form-control remaining_amount" readonly></td></tr>');
            }
        }
    });
}


function printView(param1, param2, param3) {

    if (param2 != "") {
        $('.' + param2).prop('href', '');
    }
    $('.printHide').css('display', 'none');
    var printContents = document.getElementById(param1).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    //if(param3 == 0){
    location.reload();

    //}
}

///////////////////////////////////








































