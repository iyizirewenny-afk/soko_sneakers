<?php
// delete_user.php
require_once 'functions.php';
require_once 'config.php'; 

// 1. S'assurer que seul un admin peut y accéder
require_admin($pdo); 
$admin_user = current_user($pdo); // L'utilisateur qui effectue la suppression

// 2. Récupérer l'ID de l'utilisateur à supprimer
$user_to_delete_id = (int)($_GET['id'] ?? 0);

if (!$user_to_delete_id) {
    die("ID utilisateur manquant.");
}

// 3. Empêcher l'auto-suppression
if ($user_to_delete_id == $admin_user['id']) {
    // Rediriger ou afficher un message d'erreur
    $_SESSION['error_message'] = "Erreur: Un administrateur ne peut pas supprimer son propre compte via cette interface.";
    header('Location: admin.php');
    exit;
}

try {
    // Démarrer une transaction pour s'assurer que toutes les opérations réussissent ou échouent ensemble
    $pdo->beginTransaction();

    // 4. Récupérer les informations de l'utilisateur et de ses produits avant la suppression
    $stmt_products = $pdo->prepare("SELECT image FROM products WHERE user_id = ?");
    $stmt_products->execute([$user_to_delete_id]);
    $products_images = $stmt_products->fetchAll(PDO::FETCH_COLUMN);

    // 5. Supprimer l'utilisateur de la DB
    // NOTE: Si vous avez configuré des clés étrangères en cascade sur votre DB,
    // la suppression de l'utilisateur supprimera automatiquement ses produits.
    // Nous le faisons manuellement ici pour plus de robustesse.
    
    // Supprimer d'abord les produits de cet utilisateur
    $stmt_delete_products = $pdo->prepare("DELETE FROM products WHERE user_id = ?");
    $stmt_delete_products->execute([$user_to_delete_id]);
    
    // Supprimer l'utilisateur lui-même
    $stmt_delete_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt_delete_user->execute([$user_to_delete_id]);

    // Commit la transaction
    $pdo->commit();

    // 6. Supprimer les images des produits du système de fichiers
    foreach ($products_images as $image) {
        $path = UPLOAD_DIR . $image;
        if (file_exists($path)) {
            unlink($path); 
        }
    }

    // Succès
    $_SESSION['message'] = "L'utilisateur (ID: $user_to_delete_id) et tous ses produits ont été supprimés avec succès.";
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    $_SESSION['error_message'] = "Erreur lors de la suppression de l'utilisateur: " . $e->getMessage();
}

// Redirection vers le tableau de bord
header('Location: admin.php');
exit;