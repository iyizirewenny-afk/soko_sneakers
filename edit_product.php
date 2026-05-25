<?php
// edit_product.php
require_once 'functions.php';
require_login(); 
$token = ensure_csrf_token();
$user = current_user($pdo);
$error = '';
$message = '';

// --- 1. Récupérer le produit existant ---
$product_id = (int)($_GET['id'] ?? ($_POST['product_id'] ?? 0));

if (!$product_id) {
    die('ID produit manquant.');
}

// CORRECTION 1.1: Récupérer le produit UNIQUEMENT par son ID.
// Ne pas inclure la contrainte 'user_id' dans la requête SQL.
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Produit introuvable.');
}

// CORRECTION 1.2: VÉRIFICATION DES PERMISSIONS EN PHP
$is_product_owner = ($product['user_id'] == $user['id']);
$is_admin = is_admin_user($user); // Utilise la fonction de functions.php

if (!$is_product_owner && !$is_admin) {
    // Si l'utilisateur n'est ni le propriétaire, ni un administrateur, on refuse l'accès.
    die('Vous n\'avez pas la permission de modifier ce produit.');
}

// --- 2. Traitement du formulaire de mise à jour (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalide.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        
        // Validation basique (similaire à add_product.php)
        if (empty($name) || empty($description) || empty($price) ) {
            $error = 'Veuillez remplir tous les champs obligatoires (Nom, Description, Prix).';
        }

        $new_image_file = $product['image']; // Par défaut, garder l'ancienne image

        // Gestion de l'image (si une nouvelle est téléchargée)
        $file = $_FILES['image'] ?? null;

        if (!$error && $file && $file['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5 Mo

            if ($file['size'] > $max_size) {
                $error = 'Le fichier est trop volumineux (max. 5 Mo).';
            } elseif (!in_array($file['type'], $allowed_types)) {
                $error = 'Type de fichier non supporté. Utilisez JPG, JPEG ou PNG.';
            } else {
                // Définir un nom de fichier unique et sécurisé (ex: product_ID_timestamp.ext)
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_image_name = $product['id'] . '_' . time() . '.' . strtolower($ext);
                $destination = UPLOAD_DIR . $new_image_name;

                // Déplacer le fichier téléchargé
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    
                    // Supprimer l'ancienne image si elle existe et est différente de la nouvelle
                    if (!empty($product['image']) && $product['image'] !== $new_image_name) {
                        $old_path = UPLOAD_DIR . $product['image'];
                        if (file_exists($old_path)) {
                            unlink($old_path); 
                        }
                    }
                    $new_image_file = $new_image_name;
                } else {
                    $error = 'Erreur lors du déplacement du nouveau fichier.';
                }
            }
        }

        // 3. Mise à jour de la base de données
        if (!$error) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $price, $new_image_file, $product['id']])) {
                $message = 'Le produit a été mis à jour avec succès !';
                
                // CORRECTION 2 : Recharger les données du produit après mise à jour (admin friendly)
                // Cela corrige les Warnings en bas de page
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?"); // Supprimer AND user_id = ?
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Erreur lors de la mise à jour de la base de données.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier Produit : <?= e($product['name']) ?></title>
  <style>
/* Style minimaliste pour le formulaire (vous pouvez copier le style de add_product.php) */
:root{
  --primary: #FF385C; 
  --bg: #F9FAFB;
  --card: #FFFFFF;
  --dark: #1F2937;
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
  --border-radius: 12px;
  --error: #ef4444; 
  --success: #10B981;
}
/* Base styles */
*{box-sizing:border-box}
body{
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--dark);
  margin:0;
  padding:0;
  line-height: 1.6;
}
.container{
  max-width:800px; 
  margin:40px auto; 
  padding:20px;
}
.card{
    background: var(--card);
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
}
h1{ color: var(--primary); margin-top: 0; font-size: 28px; border-bottom: 2px solid var(--border-subtle); padding-bottom: 10px; margin-bottom: 20px;}
.form-group {
    margin-bottom: 20px;
}
label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}
input[type="text"], input[type="number"], textarea, input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s;
}
input[type="number"]::-webkit-inner-spin-button, 
input[type="number"]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type="number"] { -moz-appearance: textfield; }

textarea {
    resize: vertical;
}

.btn{
    display:inline-block;
    padding:12px 20px;
    text-decoration:none;
    font-weight:700;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:background-color 0.2s;
    background:var(--primary);
    color:white;
    text-align: center;
}
.btn:hover{background: #E0354F;}

.error-message {
    background: #fee2e2;
    color: var(--error);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid var(--error);
}
.success-message {
    background: #d1fae5;
    color: var(--success);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid var(--success);
}
.current-image-preview {
    margin-top: 10px;
    max-width: 150px;
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 8px;
}
.current-image-preview img {
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: 4px;
}
  </style>
</head>
<body>
<div class="container">
    <a href="seller_account.php" style="color: var(--dark); text-decoration: none; margin-bottom: 20px; display: inline-block;">&larr; Retour à l'Espace Vendeur</a>
    <div class="card">
        <h1>Modifier le Produit : <?= e($product['name']) ?></h1>

        <?php if ($error): ?>
            <div class="error-message"><?= e($error) ?></div>
        <?php elseif ($message): ?>
            <div class="success-message"><?= e($message) ?></div>
        <?php endif; ?>

        <form action="edit_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
            <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
            
            <div class="form-group">
                <label for="name">Nom du Produit </label>
                <input type="text" name="name" id="name" value="<?= e($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description </label>
                <textarea name="description" id="description" rows="5" required><?= e($product['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Prix (Fbu) </label>
                <input type="number" name="price" id="price" step="0.01" min="0.01" value="<?= e($product['price']) ?>" required>
            </div>

            <div class="form-group">
                <label for="image">Image du Produit (Laisser vide pour garder l'image actuelle)</label>
                <input type="file" name="image" id="image" accept="image/jpeg, image/png">

                <?php if ($product['image'] && file_exists(UPLOAD_DIR . $product['image'])): ?>
                    <p style="margin-top: 10px;">Image actuelle :</p>
                    <div class="current-image-preview">
                        <img src="uploads/<?= e($product['image']) ?>" alt="Image actuelle">
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">Enregistrer les Modifications</button>
        </form>
    </div>
</div>
</body>
</html>