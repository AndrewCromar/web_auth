<?php

function create_login_code($user_id) {
    $db = get_db();
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash = hash('sha256', $code);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $stmt = $db->prepare("INSERT INTO login_codes (user_id, code_hash, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $hash, $expiry);
    $stmt->execute();

    return $code;
}