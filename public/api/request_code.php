<?php

require_once '../../backend/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    json_response(['error' => 'Invalid email address'], 400);
}

$redirect = $_POST['redirect'] ?? '';

$user = get_user_by_email($email);

if ($user) {
    $code = create_login_code($user['id']);

    if ($live) {
        $verify_url = 'https://auth.andrewcromar.org/api/verify_link.php?'
            . http_build_query(['email' => $email, 'code' => $code, 'redirect' => $redirect]);

        $html = render_email('login_code.html', [
            'name'  => $email,
            'code'  => $code,
            'expiry_minutes' => 10,
            'verify_url' => $verify_url
        ]);
        send_email($email, "Your Login Code: $code", $html);
        
        json_response(['message' => 'If this email is registered, a code has been sent.']);
    } else {
        json_response([
            'message' => 'DM: Email not sent.',
            'dev_code' => $code 
        ]);
    }
} else {
    json_response(['message' => 'If this email is registered, a code has been sent.']);
}