<?php

function current_user() {
    static $current = null;
    if ($current !== null) return $current;

    $token = $_COOKIE['session_token'] ?? null;
    if (!$token) return null;

    $user = validate_session($token);
    $current = $user ? $user : false;
    return $current;
}

function is_logged_in() {
    return current_user() !== false;
}