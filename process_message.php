<?php
// process_message.php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_login();
$user = current_user($pdo);
$sender_id = $user['id'];

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide.');
}

$product_id = (int)($_POST['product_id'] ?? 0);
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

// Validation simple
if (!$product_id || !$receiver_id || empty($message)) {
    die('Tous les champs requis ne sont pas remplis.');
}

// Sécurité : ne pas permettre d'envoyer un message à soi-même
if ($sender_id === $receiver_id) {
    die('Impossible de vous envoyer un message.');
}

// 1. Vérifier la validité des IDs (pour la sécurité)
$stmt_product = $pdo->prepare("SELECT 1 FROM products WHERE id = ?");
$stmt_product->execute([$product_id]);

$stmt_receiver = $pdo->prepare("SELECT 1 FROM users WHERE id = ?");
$stmt_receiver->execute([$receiver_id]);

if (!$stmt_product->fetch() || !$stmt_receiver->fetch()) {
    die('Produit ou Destinataire introuvable.');
}

// 2. Insérer le message (is_read sera FALSE par défaut pour le destinataire)
$stmt = $pdo->prepare("
    INSERT INTO messages (product_id, sender_id, receiver_id, message) 
    VALUES (?, ?, ?, ?)
");
$success = $stmt->execute([$product_id, $sender_id, $receiver_id, $message]);

if ($success) {
    // Rediriger vers la conversation pour voir le nouveau message
    header('Location: conversation.php?product_id=' . $product_id . '&interlocutor_id=' . $receiver_id);
    exit;
} else {
    die('Erreur lors de l\'envoi du message.');
}