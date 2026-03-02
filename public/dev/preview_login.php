<?php

require_once '../../backend/bootstrap.php';

$name = $_GET['name'] ?? 'Preview User';
$code = $_GET['code'] ?? '123456';

echo render_email(
    'login_code.html',
    [
        'name' => $name,
        'code' => $code,
        'expiry_minutes' => '10'
    ]
);