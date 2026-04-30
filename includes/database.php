<?php

$db = mysqli_connect(
    $_ENV['DB_HOST'], 
    $_ENV['DB_USER'], 
    $_ENV['DB_PASS'], 
    $_ENV['DB_NAME']);

$db->set_charset("utf8");



if (!$db) {
    error_log('MySQL connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    echo "Error interno del servidor. Intenta más tarde.";
    exit;
}
