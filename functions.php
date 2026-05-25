<?php
// functions.php (Contenu existant)
require_once 'config.php';

function ensure_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Récupère les informations de l'utilisateur connecté, y compris son statut d'administrateur.
 */
function current_user($pdo) {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?"); 
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si l'utilisateur est administrateur.
 */
function is_admin_user($user) {
    return $user && ($user['is_admin'] ?? 0); 
}

/**
 * Exige une connexion en tant qu'administrateur.
 * Redirige vers l'accueil si l'utilisateur n'est pas admin.
 */
function require_admin($pdo) {
    require_login();
    $user = current_user($pdo);
    if (!is_admin_user($user)) {
        $_SESSION['error_message'] = "Accès refusé. Seuls les administrateurs peuvent accéder à cette page.";
        header('Location: index.php'); // Doit rediriger vers index.php
        exit;
    }
}

/**
 * Compte le nombre de messages non lus pour l'utilisateur spécifié.
 */
function get_unread_message_count($pdo, $user_id) {
    if (!$user_id) {
        return 0;
    }
    try {
        // ASSUMPTION: Table 'messages' existe avec recipient_id et is_read
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Si la table messages n'existe pas encore, ceci empêche l'erreur fatale
        // error_log("Database error in get_unread_message_count: " . $e->getMessage()); 
        return 0;
    }
}