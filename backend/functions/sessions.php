<?php

function validate_session($token) {
    if (empty($token)) return false;

    $db = get_db();
    $hash = hash('sha256', $token);

    $stmt = $db->prepare("
        SELECT s.user_id, u.email 
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