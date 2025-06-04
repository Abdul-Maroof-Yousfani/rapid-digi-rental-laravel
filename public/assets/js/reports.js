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
                setTimeout(() => {
                    $('#reportList').html(response);
                }, 2000);
            }
        });
    });


});
