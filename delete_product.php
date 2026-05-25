<?php
// delete_product.php (Version mise à jour pour inclure l'autorisation Admin)
require_once 'functions.php';
require_once 'config.php'; 

require_login(); 
$user = current_user($pdo);

// 1. Déterminer si l'ID provient de POST (vendeur) ou de GET (admin)
$product_id = (int)($_POST['product_id'] ?? ($_GET['id'] ?? 0));
$token = $_POST['csrf_token'] ?? ''; // Le token est seulement envoyé par POST

if (!$product_id) {
    die("ID produit manquant.");
}

// Vérification CSRF uniquement si l'ID vient de POST (vendeur)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($token)) {
    die('Token CSRF invalide.');
}

// 2. Récupérer le produit
$stmt = $pdo->prepare("SELECT user_id, image FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Produit introuvable.');
}

// 3. Vérification des permissions
$is_product_owner = ($product['user_id'] == $user['id']);
$is_admin = is_admin_user($user);

if (!$is_product_owner && !$is_admin) {
    $_SESSION['error_message'] = "Vous n'avez pas la permission de supprimer ce produit.";
    // Rediriger l'utilisateur vers la page appropriée
    header('Location: seller_account.php');
    exit;
}

try {
    // 4. Suppression du produit de la base de données
    $stmt_delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->execute([$product_id]);

    // 5. Suppression de l'image associée du système de fichiers
    $image_file = $product['image'];
    $path = UPLOAD_DIR . $image_file;
    if (!empty($image_file) && file_exists($path)) {
        unlink($path); 
    }

    // Succès
    $_SESSION['message'] = "Le produit a été supprimé avec succès.";
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la suppression du produit: " . $e->getMessage();
}

// Redirection
// Les administrateurs retournent à la page admin, les vendeurs à leur compte.
if ($is_admin) {
    header('Location: admin.php');
} else {
    header('Location: seller_account.php');
}
exit;