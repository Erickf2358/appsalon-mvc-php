<?php

$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '';
$dbPort = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$dbSslMode = $_ENV['DB_SSLMODE'] ?? getenv('DB_SSLMODE') ?: 'prefer';

$dsn = sprintf(
    "pgsql:host=%s;port=%s;dbname=%s;sslmode=%s",
    $dbHost,
    $dbPort,
    $dbName,
    $dbSslMode
);

try {
    $db = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed');
}