$(document).ready(function(){

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('submit','#reportForm', function(e){
        e.preventDefault();
        let formData= $(this).serialize();
        $.ajax({
            url: '/get-soa-list',
            type: 'get',
            data: formData,
            success:function(response){
                $('#reportList').html(`
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                if(response){
                    $('#reportList').html(response);

                    // After content is loaded, calculate totals
                    let total = 0;
                    $('.rental-amount').each(function(){
                        total += parseFloat($(this).text().replace(/,/g, '')) || 0;
                    });

                    let net = total * 0.8;
                    $('#totalAmount').text(total.toFixed(2));
                    $('#netAmount').text(net.toFixed(2));
                    $('#printTotalAmount').text(total.toFixed(2));
                    $('#printNetAmount').text(net.toFixed(2));
                    
                } else {
                    $('#reportList').html(`
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


});
