$(function() {
    $('#login_form').on('submit', function(e) {
        e.preventDefault();
        const email = $('#login_email').val();
        const $btn = $('#login_submit');

        $btn.prop('disabled', true).text('Sending...');
        $('#output').text('');

        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect') || '';

        $.post('../api/request_code.php', { email: email, redirect: redirect })
            .done(function(data) {
                if (data.dev_code) {
                    alert("DM: Your login code is " + data.dev_code);
                }

                $('#output').css('color', 'green').text(data.message);
                localStorage.setItem('auth_email', email);

                const verifyUrl = 'verify.html' + (redirect ? '?redirect=' + encodeURIComponent(redirect) : '');
                window.location.href = verifyUrl;
            })
            .fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#message').css('color', 'red').text(err);
                $btn.prop('disabled', false).text('Send Code');
            });
    });
});
