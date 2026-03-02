<?php

require_once __DIR__ . '/../bootstrap.php';

$token = $_COOKIE['session_token'] ?? null;
$user = validate_session($token);

if (!$user) {
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $current_url = urlencode($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    
    header("Location: https://auth.andrewcromar.org/pages/login?redirect=$current_url");
    exit;
}