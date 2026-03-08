<?php
require_once '../../backend/bootstrap.php';

$token = $_COOKIE['session_token'] ?? null;
$user = validate_session($token);

if ($user) {
    json_response([
        'authenticated' => true,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email']
        ]
    ]);
} else {
    json_response(['authenticated' => false], 401);
}