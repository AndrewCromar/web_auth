<?php

require_once '../../backend/bootstrap.php';

$token = $_COOKIE['session_token'] ?? null;
$user = validate_session($token);

if (!$user) {
    json_response(['error' => 'Not authenticated'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db = get_db();
    $stmt = $db->prepare("SELECT street_1, street_2, city, state, zip FROM mailing_addresses WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    json_response(['address' => $row ?: null]);

} elseif ($method === 'POST') {
    $street_1 = trim($_POST['street_1'] ?? '');
    $street_2 = trim($_POST['street_2'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $state    = trim($_POST['state'] ?? '');
    $zip      = trim($_POST['zip'] ?? '');

    $street_1 = $street_1 !== '' ? $street_1 : null;
    $street_2 = $street_2 !== '' ? $street_2 : null;
    $city     = $city !== '' ? $city : null;
    $state    = $state !== '' ? strtoupper($state) : null;
    $zip      = $zip !== '' ? $zip : null;

    if ($state !== null && !preg_match('/^[A-Z]{2}$/', $state)) {
        json_response(['error' => 'State must be a 2-letter code'], 400);
    }

    if ($zip !== null && !preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
        json_response(['error' => 'Invalid zip code'], 400);
    }

    $db = get_db();
    $stmt = $db->prepare("INSERT INTO mailing_addresses (user_id, street_1, street_2, city, state, zip)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE street_1 = VALUES(street_1), street_2 = VALUES(street_2), city = VALUES(city), state = VALUES(state), zip = VALUES(zip)");
    $stmt->bind_param("isssss", $user['id'], $street_1, $street_2, $city, $state, $zip);
    $stmt->execute();

    json_response(['message' => 'Address saved']);

} else {
    json_response(['error' => 'Method not allowed'], 405);
}
