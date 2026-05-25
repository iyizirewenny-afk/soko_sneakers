<?php
// send_message.php
require_once 'functions.php';
require_login(); // Seuls les utilisateurs connectés peuvent envoyer des messages

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 1. Validation CSRF
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide.');
}

$product_id = (int)($_POST['product_id'] ?? 0);
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$sender_id = $_SESSION['user_id'];
$product_name = ''; // sera rempli via la BDD

// 2. Récupération des informations du produit et du vendeur
if ($product_id <= 0 || $receiver_id <= 0 || $receiver_id == $sender_id) {
    die('Erreur de paramètre.');
}

try {
    // Récupérer le nom du produit et vérifier son existence
    $stmt_prod = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt_prod->execute([$product_id]);
    $product = $stmt_prod->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die('Produit introuvable.');
    }
    $product_name = $product['name'];

    // 3. Préparer le message de contact automatique
    $initial_message = "Bonjour, je suis intéressé(e) par votre article : **" . e($product_name) . "** (ID: $product_id). Veuillez me contacter pour les modalités d'achat.";

    // 4. Insérer le message dans la base de données
    $stmt_insert = $pdo->prepare(
        "INSERT INTO messages (product_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)"
    );
    $stmt_insert->execute([$product_id, $sender_id, $receiver_id, $initial_message]);

    // 5. Redirection du client vers une confirmation ou sa boîte de réception (à créer)
    $_SESSION['success_message'] = "Votre message concernant '{$product_name}' a été envoyé au vendeur !";
    header('Location: inbox.php'); // Redirigez vers la boîte de réception du client (si créée) ou un message de confirmation
    exit;

} catch (PDOException $e) {
    die("Erreur lors de l'envoi du message : " . $e->getMessage());
}
?>