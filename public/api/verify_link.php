<?php

require_once '../../backend/bootstrap.php';

$email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
$code  = $_GET['code'] ?? '';
$redirect = $_GET['redirect'] ?? '';

if (!$email || !$code) {
    http_response_code(400);
    echo 'Invalid link.';
    exit;
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
    http_response_code(401);
    echo 'Invalid or expired code.';
    exit;
}

$update = $db->prepare("UPDATE login_codes SET used = 1 WHERE id = ?");
$update->bind_param("i", $result['id']);
$update->execute();

create_session($result['user_id']);

$default = ($live ? 'https://auth.andrewcromar.org' : '') . '/pages/dashboard.html';
$location = $redirect ?: $default;

header('Location: ' . $location);
exit;
