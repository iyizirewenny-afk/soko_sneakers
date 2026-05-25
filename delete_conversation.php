<?php
require_once 'functions.php';
require_login();

$user = current_user($pdo);

$product_id = (int)($_POST['product_id'] ?? 0);
$interlocutor_id = (int)($_POST['interlocutor_id'] ?? 0);

// Vérifie que l'utilisateur essaie de supprimer une conversation valide
if ($product_id && $interlocutor_id && $user['id'] !== $interlocutor_id) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE product_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))");
    $stmt->execute([$product_id, $user['id'], $interlocutor_id, $interlocutor_id, $user['id']]);
}

// Rediriger vers la boîte de réception après la suppression
header('Location: inbox.php');
exit;
?>