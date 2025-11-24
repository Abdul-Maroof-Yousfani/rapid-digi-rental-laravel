
$(document).ready(function(){
    $('.toggle-password').on('click', function(){
        let input = $(this).closest('.input-group').find('input');
        let icon = $(this);

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});



    const input = document.getElementById('uaePhone');

    input.addEventListener('input', function(e) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits

        // Remove +971 if already there
        if (value.startsWith('')) {
            value = value.slice(3);
        }

        // Format: +971-5X-XXXXXXX
        let formatted = '';

        if (value.length > 0) {
            formatted += '-' + value.substring(0, 2);
        }
        if (value.length > 2) {
            formatted += '-' + value.substring(2, 9);
        }

        input.value = formatted;
    });

    // On focus, auto-fill +971 if empty
    input.addEventListener('focus', function() {
        if (!input.value.startsWith('')) {
            input.value = '';
        }
    });
