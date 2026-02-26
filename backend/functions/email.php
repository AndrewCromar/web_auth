<?php

function render_email($template_name, $variables = []) {
    $template_path = __DIR__ . '/../emails/templates/' . $template_name;

    if (!file_exists($template_path)) {
        throw new Exception("Email template not found: " . $template_name);
    }

    $html = file_get_contents($template_path);

    foreach ($variables as $key => $value) {
        $safe_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $html = str_replace('{{' . $key . '}}', $safe_value, $html);
    }

    return $html;
}

function send_email($to, $subject, $html) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: auth@andrewcromar.org\r\n";

    return mail($to, $subject, $html, $headers);
}