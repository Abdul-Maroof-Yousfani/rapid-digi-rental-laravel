$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('submit', '#soaReportForm', function (e) {
        e.preventDefault();

        // Get form data
        // let fromDate = $('input[name="from_date"]').val();
        // let toDate = $('input[name="to_date"]').val();

        // // Check if both dates are selected
        // if (!fromDate || !toDate) {
        //     alert('Please select both From Date and To Date.');
        //     return; // stop the AJAX call
        // }

        let formData = $(this).serialize();
        $.ajax({
            url: '/get-soa-list',
            type: 'get',
            data: formData,
            success: function (response) {
                $('#soaReportList').html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if (response) {
                    // setTimeout(() => {
                    //     $('#soaReportList').html(response);
                    // }, 1000);

                    // // After content is loaded, calculate totals
                    // let total = 0;
                    // $('.rental-amount').each(function(){
                    //     total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    // });

                    // let net = total * 0.8;
                    // $('#totalAmount').text(total.toFixed(2));
                    // $('#netAmount').text(net.toFixed(2));
                    // $('#printTotalAmount').text(total.toFixed(2));
                    // $('#printNetAmount').text(net.toFixed(2));

                    setTimeout(() => {
                        $('#soaReportList').html(response.html);

                        // Inject investor name
                        if (response.investor_name) {
                            $('.investor_name').text(response.investor_name);
                        }
                        if (response.percentage) {
                            $('.agreePercentage').text(response.percentage);
                        }
                        if (response.till_date) {
                            $('.filterTillDate').text(response.till_date);
                            const dateParts = response.till_date.split('-'); // ["25", "July", "2025"]

                            const month = dateParts[1]; // "July"
                            const year = dateParts[2];  // "2025"
                            $('.filterTillDateMonth').text(month);
                            $('.filterTillDateYear').text(year);


                        }

                        // Calculate totals only once
                        let total = 0;
                        $('.rental-amount').each(function () {
                            total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                        });

                        let percentage = parseFloat(response.percentage || 0);
                        let net = total * ((100 - percentage) / 100);

                        $('#totalAmount').text(total.toFixed(2));
                        $('#netAmount').text(net.toFixed(2));
                        $('#printTotalAmount').text(total.toFixed(2));
                        $('#printNetAmount').text(net.toFixed(2));
                    }, 500);


                } else {
                    $('#soaReportList').html(`
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="text-center">
                                    <h3 style="color:#0d6efd;">Record Not Found</h3>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });



    $(document).on('submit', '#customerWiseSalesreportForm', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '/get-customer-wise-sales-list',
            type: 'get',
            data: formData,
            success: function (response) {
                $('#customerWiseSalesReportList').html(`
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if (response) {
                    setTimeout(() => {
                        $('#customerWiseSalesReportList').html(response);
                    }, 300);

                    // After content is loaded, calculate totals
                    let total = 0;
                    $('.rental-amount').each(function () {
                        total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    });

                    let net = total * 0.8;
                    $('#totalAmount').text(total.toFixed(2));
                    $('#netAmount').text(net.toFixed(2));
                    $('#printTotalAmount').text(total.toFixed(2));
                    $('#printNetAmount').text(net.toFixed(2));

                } else {
                    $('#customerWiseSalesReportList').html(`
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="text-center">
                                    <h3 style="color:#0d6efd;">Record Not Found</h3>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });

    $(document).ready(function () {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0'); 
        const dd = String(today.getDate()).padStart(2, '0');

        $('#toDate').val(`${yyyy}-${mm}-${dd}`);
        $('#to_date').val(`${yyyy}-${mm}-${dd}`);

        $('#fromDate').val(`${yyyy}-${mm}-01`);
        $('#from_date').val(`${yyyy}-${mm}-01`);

        


        $('#customerWiseSalesreportForm').submit();
        $('#customerWiseReceivableReportForm').submit();
        $('#salemanWiseReportForm').submit();
        $('#soaReportForm').submit();
    });


    $(document).on('submit', '#customerWiseReceivableReportForm', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '/get-customer-wise-receivable-list',
            type: 'get',
            data: formData,
            success: function (response) {
                $('#customerWiseReceivableReportList').html(`
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if (response) {
                    setTimeout(() => {
                        $('#customerWiseReceivableReportList').html(response);
                    }, 500);

                    // After content is loaded, calculate totals
                    let total = 0;
                    $('.rental-amount').each(function () {
                        total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    });

                    let net = total * 0.8;
                    $('#totalAmount').text(total.toFixed(2));
                    $('#netAmount').text(net.toFixed(2));
                    $('#printTotalAmount').text(total.toFixed(2));
                    $('#printNetAmount').text(net.toFixed(2));

                } else {
                    $('#customerWiseReceivableReportList').html(`
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="text-center">
                                    <h3 style="color:#0d6efd;">Record Not Found</h3>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });



    $(document).on('submit', '#salemanWiseReportForm', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '/get-salemen-wise-list',
            type: 'get',
            data: formData,
            success: function (response) {
                $('#salemanWiseReportList').html(`
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if (response) {
                    setTimeout(() => {
                        $('#salemanWiseReportList').html(response);
                    }, 500);

                    // After content is loaded, calculate totals
                    let total = 0;
                    $('.rental-amount').each(function () {
                        total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    });

                    let net = total * 0.8;
                    $('#totalAmount').text(total.toFixed(2));
                    $('#netAmount').text(net.toFixed(2));
                    $('#printTotalAmount').text(total.toFixed(2));
                    $('#printNetAmount').text(net.toFixed(2));

                } else {
                    $('#salemanWiseReportList').html(`
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="text-center">
                                    <h3 style="color:#0d6efd;">Record Not Found</h3>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });


    $(document).on('submit', '#investorVehicleReportForm', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '/get-investor-vehicle-list',
            type: 'get',
            data: formData,
            success: function (response) {
                $('#investorVehicleReportList').html(`
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if (response) {
                    setTimeout(() => {
                        $('#investorVehicleReportList').html(response);
                    }, 500);

                    // After content is loaded, calculate totals
                    let total = 0;
                    $('.rental-amount').each(function () {
                        total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    });

                    let net = total * 0.8;
                    $('#totalAmount').text(total.toFixed(2));
                    $('#netAmount').text(net.toFixed(2));
                    $('#printTotalAmount').text(total.toFixed(2));
                    $('#printNetAmount').text(net.toFixed(2));

                } else {
                    $('#investorVehicleReportList').html(`
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="text-center">
                                    <h3 style="color:#0d6efd;">Record Not Found</h3>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }
        });
    });


});
