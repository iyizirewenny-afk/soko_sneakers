<?php
// process_login.php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide');
}

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

if (!$identity || !$password) {
    die('Tous les champs sont requis.');
}

// find user by username or email
$stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$identity, $identity]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    die('Identifiants incorrects.');
    
}

// success
$_SESSION['user_id'] = $user['id'];
header('Location: index.php');
exit;
