<?php
// process_register.php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide');
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
// NOUVEAU: Récupération des champs du vendeur
$location = trim($_POST['location'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');

// CORRECTION: Assurez-vous que tous les champs sont présents pour l'inscription
if (!$username || !$email || !$password || !$location || !$contact_number) {
    die('Tous les champs (nom d\'utilisateur, email, mot de passe, localisation et contact) sont requis.');
}

// basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email invalide.');
}

if (strlen($password) < 6) {
    die('Le mot de passe doit contenir au moins 6 caractères.');
}

// check duplicates
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    die('Nom d\'utilisateur ou email déjà utilisé.');
}

// create user
$hash = password_hash($password, PASSWORD_DEFAULT);
// CORRECTION: La requête INSERT doit inclure les 5 colonnes
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, location, contact_number) VALUES (?, ?, ?, ?, ?)");
// CORRECTION: L'exécution doit inclure les 5 variables
$stmt->execute([$username, $email, $hash, $location, $contact_number]);

// login user
$_SESSION['user_id'] = $pdo->lastInsertId();
header('Location: index.php');
exit;