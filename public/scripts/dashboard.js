function formatPhone(value) {
    var digits = value.replace(/\D/g, '').substring(0, 10);
    if (digits.length > 6) return '(' + digits.substring(0,3) + ') ' + digits.substring(3,6) + '-' + digits.substring(6);
    if (digits.length > 3) return '(' + digits.substring(0,3) + ') ' + digits.substring(3);
    if (digits.length > 0) return '(' + digits;
    return '';
}

function renderCompletion(u, addr) {
    var items = [
        { label: 'Name', done: !!(u.first_name && u.last_name), page: 'myinfo' },
        { label: 'Birthday', done: !!(u.date_of_birth), page: 'myinfo' },
        { label: 'Phone', done: !!u.phone, page: 'myinfo' },
        { label: 'Mailing Address', done: !!(addr.street_1 && addr.city && addr.state && addr.zip), page: 'mailing' }
    ];

    var doneCount = items.filter(function(i) { return i.done; }).length;

    if (doneCount === items.length) {
        $('.profile-completion').hide();
        return;
    }

    var pct = Math.round((doneCount / items.length) * 100);
    $('.completion-status').text(doneCount + ' of ' + items.length + ' complete');
    $('.progress-fill').css('width', pct + '%');

    var $list = $('.checklist').empty();
    items.forEach(function(item) {
        var $item = $('<div class="checklist-item">').toggleClass('done', item.done);
        $item.append('<span class="checklist-label">' + item.label + '</span>');
        if (item.done) {
            $item.append('<span class="checkmark">&#10003;</span>');
        } else {
            var $btn = $('<button class="complete-btn">Complete</button>');
            $btn.on('click', function() {
                $('.nav-btn[data-page="' + item.page + '"]').click();
            });
            $item.append($btn);
        }
        $list.append($item);
    });

    $('.profile-completion').show();
}

$(function() {
    var greeting = 'Hello!';

    $.get('../api/validate_session.php')
        .done(function(data) {
            $('#loading').hide();
            if (data.authenticated) {
                var u = data.user;

                var hasName = u.first_name || u.last_name;
                var nameText = hasName
                    ? ((u.first_name || '') + ' ' + (u.last_name || '')).trim()
                    : u.email;

                greeting = 'Hello, ' + nameText + '!';
                $('#page-title').text(greeting);

                $('#info_first_name').val(u.first_name || '');
                $('#info_last_name').val(u.last_name || '');
                $('#info_birthday').val(u.date_of_birth || '');
                $('#info_phone').val(formatPhone(u.phone || ''));
                $('#info_email').val(u.email || '');

                $('#info_phone').on('input', function() {
                    var pos = this.selectionStart;
                    var before = this.value;
                    var formatted = formatPhone(before);
                    $(this).val(formatted);
                    var diff = formatted.length - before.length;
                    this.setSelectionRange(pos + diff, pos + diff);
                });

                $.get('../api/mailing_address.php').done(function(resp) {
                    if (resp.address) {
                        $('#mail_street_1').val(resp.address.street_1 || '');
                        $('#mail_street_2').val(resp.address.street_2 || '');
                        $('#mail_city').val(resp.address.city || '');
                        $('#mail_state').val(resp.address.state || '');
                        $('#mail_zip').val(resp.address.zip || '');
                    }
                    var addr = resp.address || {};
                    renderCompletion(u, addr);
                });

                $('#dashboard').show();

                var hash = window.location.hash.replace('#', '');
                if (hash) {
                    var btn = $('.nav-btn[data-page="' + hash + '"]');
                    if (btn.length) btn.click();
                }
            } else {
                $('#logged_out').css('display', 'flex');
            }
        })
        .fail(function() {
            $('#loading').hide();
            $('#logged_out').css('display', 'flex');
        });

    function refreshCompletion() {
        var u = {
            first_name: $('#info_first_name').val().trim(),
            last_name: $('#info_last_name').val().trim(),
            date_of_birth: $('#info_birthday').val().trim(),
            phone: $('#info_phone').val().replace(/\D/g, '')
        };
        var addr = {
            street_1: $('#mail_street_1').val().trim(),
            city: $('#mail_city').val().trim(),
            state: $('#mail_state').val().trim(),
            zip: $('#mail_zip').val().trim()
        };
        renderCompletion(u, addr);
    }

    // Nav switching
    $(document).on('click', '.nav-btn[data-page]', function() {
        var page = $(this).data('page');

        $('.nav-btn[data-page]').removeClass('active');
        $(this).addClass('active');

        $('.page').removeClass('active');
        $('#page-' + page).addClass('active');

        $('#page-title').text($(this).data('title') || greeting);

        if (page === 'home') refreshCompletion();

        window.location.hash = page;
    });

    $(window).on('hashchange', function() {
        var hash = window.location.hash.replace('#', '');
        var btn = $('.nav-btn[data-page="' + hash + '"]');
        if (btn.length && !btn.hasClass('active')) btn.click();
    });

    // Save personal info
    $(document).on('click', '#save_personal_btn', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Saving...');

        $.post('../api/update_profile.php', {
            first_name: $('#info_first_name').val(),
            last_name: $('#info_last_name').val(),
            date_of_birth: $('#info_birthday').val()
        })
        .done(function() {
            var first = $('#info_first_name').val().trim();
            var last = $('#info_last_name').val().trim();
            var nameText = (first || last) ? (first + ' ' + last).trim() : '';

            if (nameText) {
                greeting = 'Hello, ' + nameText + '!';
            }

            btn.text('Saved!');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        })
        .fail(function() {
            btn.text('Error');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        });
    });

    // Save phone
    $(document).on('click', '#save_phone_btn', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Saving...');

        $.post('../api/update_profile.php', {
            phone: $('#info_phone').val().replace(/\D/g, '')
        })
        .done(function() {
            btn.text('Saved!');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        })
        .fail(function() {
            btn.text('Error');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        });
    });

    // Save mailing address
    $(document).on('click', '#save_mailing_btn', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Saving...');

        $.post('../api/mailing_address.php', {
            street_1: $('#mail_street_1').val(),
            street_2: $('#mail_street_2').val(),
            city: $('#mail_city').val(),
            state: $('#mail_state').val(),
            zip: $('#mail_zip').val()
        })
        .done(function() {
            btn.text('Saved!');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        })
        .fail(function() {
            btn.text('Error');
            setTimeout(function() { btn.prop('disabled', false).text('Save Changes'); }, 1500);
        });
    });

    // Sign out
    $(document).on('click', '#logout_btn', function() {
        window.location.href = 'logout.html';
    });

    // Login
    $(document).on('click', '#login_btn', function() {
        window.location.href = 'login.html';
    });
});
