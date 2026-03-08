$(function() {
    $.get('../api/validate_session.php')
        .done(function(data) {
            $('#loading').hide();
            if (data.authenticated) {
                $('#user_id').text(data.user.id);
                $('#user_email').text(data.user.email);
                $('#logged_in').show();
            } else {
                $('#logged_out').show();
            }
        })
        .fail(function() {
            $('#loading').hide();
            $('#logged_out').show();
        });

    $('#logout_btn').on('click', function() {
        window.location.href = 'logout.html';
    });

    $('#login_btn').on('click', function() {
        window.location.href = 'login.html';
    });
});
