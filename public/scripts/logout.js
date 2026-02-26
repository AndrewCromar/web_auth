$(function() {
    $.post('../api/logout.php')
        .always(function() {
            localStorage.removeItem('auth_email');

            const params = new URLSearchParams(window.location.search);
            const redirectPath = params.get('redirect') || 'login.html';

            $('#status').text('You have been logged out. Redirecting...');

            setTimeout(function() {
                window.location.href = redirectPath;
            }, 1000);
        });
});
