<?php

require_once '../../backend/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    json_response(['error' => 'Invalid email address'], 400);
}

$user = get_user_by_email($email);

if ($user) {
    $code = create_login_code($user['id']);
    
    if ($live) {
        $html = render_email('login_code.html', [
            'name'  => $email,
            'code'  => $code,
            'expiry_minutes' => 10
        ]);
        send_email($email, "Your Login Code", $html);
        
        json_response(['message' => 'If this email is registered, a code has been sent.']);
    } else {
        json_response([
            'message' => 'DEV MODE: Email not sent.',
            'dev_code' => $code 
        ]);
    }
} else {
    json_response(['message' => 'If this email is registered, a code has been sent.']);
}