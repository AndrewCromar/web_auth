<?php
require_once '../../backend/bootstrap.php';

$token = $_COOKIE['session_token'] ?? null;

if ($token) {
    $db = get_db();
    $hash = hash('sha256', $token);
    
    $stmt = $db->prepare("UPDATE sessions SET revoked = 1 WHERE token_hash = ?");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
}

setcookie('session_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '.andrewcromar.org',
    'httponly' => true,
    'samesite' => 'Lax'
]);

json_response(['message' => 'Logged out']);