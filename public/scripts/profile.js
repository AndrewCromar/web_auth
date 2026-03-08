$(function() {
    $.get('../api/validate_session.php')
        .done(function(data) {
            $('#loading').hide();
            if (data.authenticated) {
                var u = data.user;
                $('#profile_email').val(u.email);
                $('#profile_first_name').val(u.first_name || '');
                $('#profile_last_name').val(u.last_name || '');
                $('#profile_dob').val(u.date_of_birth || '');
                $('#profile_phone').val(u.phone || '');
                $('#profile_form_container').show();
            } else {
                $('#logged_out').show();
            }
        })
        .fail(function() {
            $('#loading').hide();
            $('#logged_out').show();
        });

    $('#profile_form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#profile_submit');
        $btn.prop('disabled', true).text('Saving...');
        $('#output').text('');

        $.post('../api/update_profile.php', {
            first_name: $('#profile_first_name').val(),
            last_name: $('#profile_last_name').val(),
            date_of_birth: $('#profile_dob').val(),
            phone: $('#profile_phone').val()
        })
            .done(function(data) {
                $('#output').css('color', 'var(--accent)').text('Profile saved!');
                $btn.prop('disabled', false).text('Save');
            })
            .fail(function(xhr) {
                var err = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#output').css('color', 'red').text(err);
                $btn.prop('disabled', false).text('Save');
            });
    });

    $('#login_btn').on('click', function() {
        window.location.href = 'login.html';
    });
});
