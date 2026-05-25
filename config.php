<?php
// config.php
session_start();

// Modify these to match your local setup
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sokosneakers');
define('DB_USER', 'root');
define('DB_PASS', ''); // default XAMPP: empty

// Uploads folder
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Simple helper to escape output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
