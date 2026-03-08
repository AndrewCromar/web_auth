$(function() {
    $('#verify_form').on('submit', function(e) {
        e.preventDefault();
        const email = localStorage.getItem('auth_email');
        const code = $('#verify_code').val();

        if (!email) {
            alert('Session lost. Please try logging in again.');
            window.location.href = 'login.html';
            return;
        }

        $.post('../api/verify_code.php', { email: email, code: code })
            .done(function(data) {
                $('#output').css('color', 'green').text('Success! Redirecting...');
                const params = new URLSearchParams(window.location.search);
                window.location.href = params.get('redirect') || 'dashboard.html';
            })
            .fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.error : 'Invalid code';
                $('#output').css('color', 'red').text(err);
            });
    });
});
