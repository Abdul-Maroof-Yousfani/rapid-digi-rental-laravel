$(document).ready(function() {

    $(document).on('change', '.invoice_type', function() {
        const row = $(this).closest('tr');
        const dateInputs = row.find('.datemask');

        if ($(this).val() === '2') {
            dateInputs.prop('required', true);
            dateInputs.prop('disabled', false);
        } else {
            dateInputs.prop('required', false);
            dateInputs.prop('disabled', true);
        }
    });

    $('.invoice_type').trigger('change');

});
