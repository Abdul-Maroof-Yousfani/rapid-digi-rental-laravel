$(document).ready(function() {

    $(document).on('change', '.invoice_type', function() {
        const row = $(this).closest('tr');
        const dateInputs = row.find('.datemask');
        const vehicleType = row.find('.vehicletypes');

        if ($(this).val() === '2') {
            dateInputs.prop('required', true);
            dateInputs.prop('disabled', false);
            vehicleType.prop('disabled', true);
        } else {
            dateInputs.prop('required', false);
            dateInputs.prop('disabled', true);
            vehicleType.prop('disabled', false);
        }
    });

    $('.invoice_type').trigger('change');

});
