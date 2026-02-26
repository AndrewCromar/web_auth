$(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#email').val();
        const $btn = $('#btnSubmit');

        $btn.prop('disabled', true).text('Sending...');
        $('#message').text('');

        $.post('../api/request_code.php', { email: email })
            .done(function(data) {
                if (data.dev_code) {
                    alert("Dev Mode: Your login code is " + data.dev_code);
                }

                $('#message').css('color', 'green').text(data.message);
                localStorage.setItem('auth_email', email);

                setTimeout(function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirect = urlParams.get('redirect');
                    const verifyUrl = 'verify.html' + (redirect ? '?redirect=' + encodeURIComponent(redirect) : '');
                    window.location.href = verifyUrl;
                }, 1500);
            })
            .fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#message').css('color', 'red').text(err);
                $btn.prop('disabled', false).text('Send Code');
            });
    });
});
