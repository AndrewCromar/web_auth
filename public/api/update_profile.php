<?php

require_once '../../backend/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$token = $_COOKIE['session_token'] ?? null;
$user = validate_session($token);

if (!$user) {
    json_response(['error' => 'Not authenticated'], 401);
}

$allowed_fields = [
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'date_of_birth' => 'date_of_birth',
    'phone' => 'phone',
];

$sets = [];
$types = '';
$values = [];

foreach ($allowed_fields as $post_key => $column) {
    if (!array_key_exists($post_key, $_POST)) {
        continue;
    }

    $val = trim($_POST[$post_key]);
    $val = $val !== '' ? $val : null;

    if ($column === 'date_of_birth' && $val !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        json_response(['error' => 'Invalid date format'], 400);
    }

    $sets[] = "$column = ?";
    $types .= 's';
    $values[] = $val;
}

if (empty($sets)) {
    json_response(['error' => 'No fields to update'], 400);
}

$types .= 'i';
$values[] = $user['id'];

$db = get_db();
$sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$values);
$stmt->execute();

json_response(['message' => 'Profile updated']);
