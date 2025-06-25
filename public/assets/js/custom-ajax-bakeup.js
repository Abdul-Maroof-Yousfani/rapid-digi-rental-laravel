$(document).ready(function(){

    $('#booking_id').trigger('change');

    // (-) Not Enter in any Number Input Fields
    $(document).on('keypress', '.amount_receive', function (e) {
        if (e.key === '-' || e.which === 45) {
            e.preventDefault();
        }
    });

    // Render invoices and vehicles against booking in payment form
    $(document).on('change', '.payment_method', function(){
        var paymentMethod= $(this).val();
        if(paymentMethod==3){ $('.bank_id').removeAttr('disabled'); }
        else { $('.bank_id').attr('disabled', true).val('');}
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
        var enteredValue = parseFloat($(this).val());
        var restrictValue = parseFloat($('.restrict').val());
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

        // Agar entered value, restrict se zyada hai
        if (enteredValue > restrictValue) {
            $(this).val(restrictValue); // Automatically restrict it
            enteredValue = restrictValue; // Optional: update local variable too
        }

        let remainingAmount = parseFloat($(this).val()) || 0;
        let depositAmount = parseFloat($('.deposit_amount').val()) || 0;
        $('#booking_detail tr').each(function () {
            let invoiceTotalCell = $(this).find('.invoice_total');
            if (invoiceTotalCell.length === 0)return;

            // let invoiceAmount = parseFloat($(this).find('.invoice_total').text()) || 0;
            let invoiceTotal = parseFloat(invoiceTotalCell.text()) || 0;
            let inputField = $(this).find('.invPaidAmount');
            let depositCheckbox = $(this).find('.add_deposit');
            let originalPaid = parseFloat(inputField.attr('data-original')) || 0;

            // Already fully paid — skip
            if (originalPaid.toFixed(2) === invoiceTotal.toFixed(2)) {
                inputField.val(originalPaid.toFixed(2));
                $(this).css('background-color', '#d4edda');
                depositCheckbox.prop('disabled', true);
                return;
            }

            let remainingForThis = invoiceTotal - originalPaid;
            if (remainingAmount >= remainingForThis) {
                inputField.val((originalPaid + remainingForThis).toFixed(2));
                remainingAmount -= remainingForThis;
                $(this).css('background-color', '#d4edda');
                depositCheckbox.prop('disabled', true);
            } else if (remainingAmount > 0) {
                inputField.val((originalPaid + remainingAmount).toFixed(2));
                remainingAmount = 0;
                $(this).css('background-color', '#fff3cd');
                depositCheckbox.prop('disabled', false);
            } else {
                inputField.val(originalPaid.toFixed(2));
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


function bookingChange(){
    $('.amount_receive').removeAttr('disabled');
    var bookingID= $('#booking_id').val();
    $.ajax({
        url : '/get-booking-detail/'+bookingID,
        type: 'GET',
        success:function(response){
        $('#vehicles').html('');
        $('#booking_detail').html('');
            if(response){
                $('.booking_amount').val(response.booking_amount);
                $('.initial_deposit').val(response.deposit_amount);
                $('.deposit_amount').val(response.remaining_deposit);
                $('.remaining_deposit').val(response.remaining_deposit);
                $('.customer_name').val(response.customer);
                $('.pending_amount').val(parseFloat(response.remaining_amount).toFixed(2));
                $('.restrict').val(parseFloat(response.remaining_amount).toFixed(2));

                // Get Payment ID
                $('.payment_id').val(response.payment_id);

                if(response.paid_amount > 0){
                    $('.already_paid').html('');
                    $('.already_paid').append(
                        '<div class="form-group">'+
                            '<label for="">Already Paid</label><br>'+
                            '<input type="number" placeholder="" value="'+response.paid_amount+'" name="" class="form-control" min="0" step="0.01">'+
                        '</div>');
                } else {
                    $('.already_paid').html('');
                }
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
                    var paid = parseFloat(invoice.paid_amount) || 0;
                    var total = parseFloat(invoice.invoice_amount) || 0;
                    var deposit = parseFloat(invoice.deposit_amount) || 0;
                    var remainingDeposit = parseFloat(response.remaining_deposit) || 0;

                    // Get PaymentData ID
                    var paymentDataID= invoice.payment_data_id;

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
                        '<td><input type="hidden" name="paymentData_id[]" value="'+paymentDataID+'">' + invoice.zoho_invoice_number + '</td>' +
                        '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
                        '<td>' + invoice.summary.fine_qty + ' | ' + invoice.summary.fine_amount + '</td>' +
                        '<td>' + invoice.summary.renew_amount + '</td>' +
                        '<td>' + (invoice.summary.rent_amount).toFixed(2) + '</td>' +
                        '<td>' + invoice.invoice_status + '</td>' +
                        '<td class="invoice_total">' + invoice.invoice_amount + '</td>' +
                        '<td class="text-center">'+
                            '<div class="custom-control custom-checkbox">' +
                            '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '"' +(disableCheckbox ? ' disabled' : '') + '><input type="hidden" class="addDepositAmount form-control" name="addDepositAmount[]" value="'+deposit+'">' +
                            '<label class="custom-control-label" for="depositCheck' + index + '"></label></div>'+
                        '</td>'+
                        '<td class="recieve_amount">'+
                            '<input type="hidden" name="invoice_id[]" value="'+invoice.invoice_id+'">'+
                            '<input type="hidden" name="invoice_amount[]" value="'+invoice.invoice_amount+'">'+
                            '<input type="text" name="invPaidAmount[]" value="' + paid.toFixed(2) + '" data-original="' + paid.toFixed(2) + '" class="form-control invPaidAmount" readonly>'+
                        '</td></tr>';
                    $('#booking_detail').append(row);
                });
                $('#booking_detail').append('<tr><td colspan="8" class="text-right">Sub total</td>'+
                    '<td><input type="number" value="" name="amount_receive" class="form-control insubtot" readonly></td></tr>'+
                    '<tr style="display: none"><td colspan="8" class="text-right">Remaining Amount</td>'+
                    '<td><input type="number" value="" name="" class="form-control remaining_amount" readonly></td></tr>');
            }
        }
    });
}









// function bookingChange(){
//     $('.amount_receive').removeAttr('disabled');
//     var bookingID= $('#booking_id').val();
//     $.ajax({
//         url : '/get-booking-detail/'+bookingID,
//         type: 'GET',
//         success:function(response){
//         $('#vehicles').html('');
//         $('#booking_detail').html('');
//             if(response){
//                 $('.booking_amount').val(response.booking_amount);
//                 $('.deposit_amount').val(response.deposit_amount);
//                 $('.customer_name').val(response.customer);
//                 $('.pending_amount').val(response.remaining_amount);
//                 $.each(response.vehicle, function(index, vehicles) {
//                     var row = '<tr>' +
//                                 '<th scope="row">' + (index + 1) + '</th>' +
//                                 '<td>' + vehicles.name + '</td>' +
//                                 '<td>' + vehicles.type + '</td>' +
//                             '</tr>';
//                     $('#vehicles').append(row);
//                 });
//                 let subtotal = 0;
//                 $.each(response.invoice_detail, function(index, invoice){
//                     var paid = parseFloat(invoice.paid_amount) || 0;
//                     var row = '<tr>' +
//                         '<td>' + invoice.zoho_invoice_number + '</td>' +
//                         '<td>' + invoice.summary.salik_qty + ' | ' + invoice.summary.salik_amount + '</td>' +
//                         '<td>' + invoice.summary.fine_qty + ' | ' + invoice.summary.fine_amount + '</td>' +
//                         '<td>' + invoice.summary.renew_amount + '</td>' +
//                         '<td>' + invoice.summary.rent_amount + '</td>' +
//                         '<td>' + invoice.invoice_status + '</td>' +
//                         '<td class="invoice_total">' + invoice.invoice_amount + '</td>' +
//                         '<td class="text-center">'+
//                             '<div class="custom-control custom-checkbox">' +
//                             '<input type="checkbox" class="custom-control-input add_deposit" id="depositCheck' + index + '"><input type="hidden" class="addDepositAmount form-control" name="addDepositAmount[]" value="">' +
//                             '<label class="custom-control-label" for="depositCheck' + index + '"></label></div>'+
//                         '</td>'+
//                         '<td class="recieve_amount">'+
//                             '<input type="hidden" name="invoice_id[]" value="'+invoice.invoice_id+'">'+
//                             '<input type="hidden" name="invoice_amount[]" value="'+invoice.invoice_amount+'">'+
//                             '<input type="text" name="invPaidAmount[]" value="' + paid.toFixed(2) + ' class="form-control invPaidAmount" readonly>'+
//                         '</td></tr>';
//                     $('#booking_detail').append(row);
//                 });
//                 $('#booking_detail').append('<tr><td colspan="8" class="text-right">Sub total</td>'+
//                     '<td><input type="number" value="" name="amount_receive" class="form-control insubtot" readonly></td></tr>'+
//                     '<tr><td colspan="8" class="text-right">Remaining Amount</td>'+
//                     '<td><input type="number" value="" name="" class="form-control remaining_amount" readonly></td></tr>');
//             }
//         }
//     });
// }








    // $(document).on('input', '.amount_receive', function () {
    //     if (!$(this).val()) {
    //         $('.pending_amount').val('');
    //         $('.remaining_amount').val('');
    //         $('.insubtot').val('');
    //         $('.invPaidAmount').val(0);
    //         $('#booking_detail tr').each(function () {
    //             if ($(this).find('.invoice_total').length > 0) {
    //                 $(this).css('background-color', '');
    //             }
    //         });
    //         return;
    //     }
    //     let remainingAmount = parseFloat($(this).val()) || 0;
    //     $('#booking_detail tr').each(function () {
    //         let invoiceTotalCell = $(this).find('.invoice_total');
    //         if (invoiceTotalCell.length === 0) {
    //             return;
    //         }
    //         let invoiceAmount = parseFloat($(this).find('.invoice_total').text()) || 0;
    //         let inputField = $(this).find('.invPaidAmount');
    //         let depositCheckbox = $(this).find('.add_deposit');
    //         if (remainingAmount >= invoiceAmount) {
    //             inputField.val(invoiceAmount.toFixed(2));
    //             remainingAmount -= invoiceAmount;
    //             $(this).css('background-color', '#d4edda');
    //             depositCheckbox.prop('disabled', true);
    //         } else if (remainingAmount > 0) {
    //             inputField.val(remainingAmount.toFixed(2));
    //             remainingAmount = 0;
    //             $(this).css('background-color', '#fff3cd');
    //             depositCheckbox.prop('disabled', false);
    //         } else {
    //             inputField.val(0);
    //             $(this).css('background-color', '#fff3cd');
    //             depositCheckbox.prop('disabled', false);
    //         }
    //     });
    //     recalculateTotals();
    //     $('.remaining_amount').val(remainingAmount.toFixed(2));


    // });
