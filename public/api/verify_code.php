<?php

require_once '../../backend/bootstrap.php';

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$code  = $_POST['code'] ?? '';

if (!$email || !$code) {
    json_response(['error' => 'Email and code required'], 400);
}

$db = get_db();
$hash = hash('sha256', $code);

$stmt = $db->prepare("
    SELECT lc.id, lc.user_id 
    FROM login_codes lc
    JOIN users u ON lc.user_id = u.id
    WHERE u.email = ? 
      AND lc.code_hash = ? 
      AND lc.used = 0 
      AND lc.expires_at > NOW()
    LIMIT 1
");
$stmt->bind_param("ss", $email, $hash);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    json_response(['error' => 'Invalid or expired code'], 401);
}

$update = $db->prepare("UPDATE login_codes SET used = 1 WHERE id = ?");
$update->bind_param("i", $result['id']);
$update->execute();

create_session($result['user_id']);

json_response(['message' => 'Login successful']);