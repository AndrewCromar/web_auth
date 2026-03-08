<?php
require_once '../../backend/bootstrap.php';

$token = $_COOKIE['session_token'] ?? null;
$user = validate_session($token);

if ($user) {
    json_response([
        'authenticated' => true,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'date_of_birth' => $user['date_of_birth'],
            'phone' => $user['phone']
        ]
    ]);
} else {
    json_response(['authenticated' => false], 401);
}