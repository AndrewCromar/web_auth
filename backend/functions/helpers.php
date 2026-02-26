<?php

function get_db() {
    static $db;
    if ($db === null) {
        global $DB_servername, $DB_username, $DB_password, $DB_name;
        $db = new mysqli($DB_servername, $DB_username, $DB_password, $DB_name);
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }
        $db->set_charset("utf8mb4");
    }
    return $db;
}

function json_response($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}