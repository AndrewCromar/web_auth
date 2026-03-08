<?php

require_once '../../backend/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$token = $_POST['token'] ?? '';

if (!$token) {
    json_response(['authenticated' => false], 401);
}

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
