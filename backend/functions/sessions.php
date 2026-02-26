<?php

function create_session($user_id) {
    global $live;
    $db = get_db();
    
    $token = bin2hex(random_bytes(32)); 
    $hash = hash('sha256', $token);
    
    $expiry_timestamp = strtotime('+30 days');
    $expiry_date = date('Y-m-d H:i:s', $expiry_timestamp);
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $db->prepare("INSERT INTO sessions (user_id, token_hash, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $hash, $expiry_date, $ip, $ua);
    $stmt->execute();

    $cookie_options = [
        'expires'  => $expiry_timestamp,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    if ($live) {
        $cookie_options['domain'] = '.andrewcromar.org';
        $cookie_options['secure'] = true;
    } else {
        $cookie_options['domain'] = ''; 
        $cookie_options['secure'] = false; 
    }

    setcookie('session_token', $token, $cookie_options);

    return $token;
}

function validate_session($token) {
    if (empty($token)) return false;

    $db = get_db();
    $hash = hash('sha256', $token);

    $stmt = $db->prepare("
        SELECT s.user_id AS id, u.email 
        FROM sessions s
        JOIN users u ON s.user_id = u.id
        WHERE s.token_hash = ? 
          AND s.revoked = 0 
          AND s.expires_at > NOW()
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}