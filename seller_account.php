<?php
// seller_account.php
require_once 'functions.php';
require_login(); // S'assurer que seul un utilisateur connecté peut y accéder

// 1. CORRECTION: AJOUT de la colonne is_admin à la requête SQL
$stmt_user = $pdo->prepare("SELECT id, username, email, location, contact_number, profile_image, is_admin FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Erreur: Utilisateur non trouvé.");
}

// Définir la source de l'image de profil
$profile_image_src = 'uploads/' . e($user['profile_image']);
if (empty($user['profile_image']) || !file_exists(UPLOAD_DIR . $user['profile_image'])) {
    // URL d'une image par défaut si le champ est vide ou le fichier n'existe pas
    $profile_image_src = 'https://via.placeholder.com/100?text=' . substr(e($user['username']), 0, 1);
}

// 2. Récupérer UNIQUEMENT les produits de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Mon Compte Vendeur - SokoSneakers</title>
  <style>
/* style.css - SokoSneakers Modern & Clean Design */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

:root{
  /* Couleurs */
  --primary: #FF385C; /* Rouge vibrant (pour les boutons, liens importants) */
  --secondary: #2C3E50; /* Bleu marine/foncé pour l'accentuation textuelle */
  --bg: #F0F2F5; /* Gris clair moderne pour le fond */
  --card: #FFFFFF; /* Blanc pur pour les cartes/conteneurs */
  --dark: #1F2937; /* Gris très foncé pour le texte */
  --border-subtle: #E0E0E0;
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
  --border-radius: 12px;
}
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
  max-width:1200px;
  margin:30px auto;
  padding:0 20px;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-subtle);
}
.logo { 
   
    font-weight:800; 
    color:var(--primary); 
    font-size:38px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
    font-family:'Brush Script MT',cursive;
    text-shadow: 2px 2px #b2b2b2;
}
.btn{
    display:inline-block;
    padding:8px 16px;
    text-decoration:none;
    font-weight:600;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:background-color 0.2s;
    background:var(--primary);
    color:white;
    margin-left: 10px;
}
.btn:hover{background: #E0354F;}
.btn.secondary { background:#666; }
.btn.secondary:hover { background:#444; }

/* Styles spécifiques à l'Espace Vendeur */
.stats {
    background: var(--card);
    padding: 25px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    display: flex; /* Utilisation de Flexbox */
    align-items: center;
    gap: 25px;
}

/* 3. Style de la Photo de Profil */
/* Ajouter ce style à seller_account.php dans la balise <style> */

.profile-image-container {
    flex-shrink: 0;
    text-align: center; /* Centrer le bouton en dessous de l'image */
}
.profile-image {
    width: 100px; 
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
    box-shadow: var(--shadow-light);
    margin-bottom: 10px; /* Espace entre l'image et le bouton */
    display: block; /* Important pour centrer si on change le texte-align du parent */
    margin-left: auto;
    margin-right: auto;
}
.btn-edit-profile {
    display: block;
    width: 100px;
    padding: 4px 8px;
    font-size: 12px;
    background: #6B7280; /* Gris plus neutre pour le bouton d'édition */
}
.btn-edit-profile:hover {
    background: #4B5563;
}

.seller-details {
    flex-grow: 1; /* Permet aux détails de prendre l'espace restant */
}

.seller-details h2 { 
    margin: 0 0 5px 0; 
    font-size: 26px;
    color: var(--dark);
}
.seller-details p {
    margin: 3px 0;
    font-size: 15px;
    color: #6B7280; /* Gris plus doux pour les métadonnées */
}
.seller-details strong {
    font-weight: 600;
    color: var(--dark);
}
/* Ajouter ou modifier*/

.product-card {
    /* Assurez-vous que le product-card est un conteneur flexible ou gère bien les actions */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Pousse les actions vers le bas */
}

.actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.actions .btn {
    flex-grow: 1; /* Permet aux boutons de prendre la même largeur */
    padding: 8px;
    font-size: 14px;
    text-align: center;
    text-decoration: none; /* Pour le lien Modifier */
}

.edit-btn {
    background-color: #3B82F6; /* Bleu pour Modifier */
}

.edit-btn:hover {
    background-color: #2563EB;
}

.delete-btn {
    background-color: #EF4444; /* Rouge pour Supprimer */
    color: white;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    background-color: #DC2626;
}

/* Styles de la grille de produits (inchangés ou adaptés de vos autres fichiers) */
.products-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
    gap:25px;
}
.product-card{
    background:var(--card);
    border-radius:var(--border-radius);
    overflow:hidden;
    text-decoration:none;
    color:var(--dark);
    box-shadow:var(--shadow-light);
    transition:transform 0.2s, box-shadow 0.2s;
}
.product-card:hover{
    transform:translateY(-3px);
    box-shadow:var(--shadow-hover);
}
.product-card img{
    width:100%;
    height:200px;
    object-fit:cover;
    display:block;
}
.product-info{
    padding:15px;
}
.product-info h3{
    margin-top:0;
    font-size:18px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.product-info .price{
    font-size:20px;
    font-weight:700;
    color:var(--primary);
    margin-top:10px;
}
.footer { text-align:center; color:#666; margin:40px 0 20px; font-size:14px; }
@media (max-width: 600px) {
    .stats {
        flex-direction: column;
        text-align: center;
    }
    .seller-details {
        text-align: center;
    }
}
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav>
        <?php 
        // CORRECTION: Utilisation de la fonction is_admin_user définie dans functions.php
        if (is_admin_user($user)): ?>
            <a href="admin.php" class="btn" style="background: #3498db;">Administration 👑</a>
        <?php endif; ?>
      <a href="inbox.php" class="btn" >Messagerie</a> 
      <a href="add_product.php" class="btn">Ajouter un produit</a>
      <a href="logout.php" class="btn secondary">Déconnexion</a>
    </nav>
</header>

  <div class="stats">
    <div class="profile-image-container">
        <img src="<?= $profile_image_src ?>" alt="Photo de profil de <?= e($user['username']) ?>" class="profile-image">
        <a href="upload_profile_image.php" class="btn secondary btn-edit-profile">Modifier la photo</a>
    </div>
    
    <div class="seller-details">
        <h2><?= e($user['username']) ?></h2>
        <p><strong>Localisation :</strong> <?= e($user['location'] ?? 'Non spécifiée') ?></p>
        <p><strong>Contact :</strong> <?= e($user['contact_number'] ?? 'Non spécifié') ?></p>
        <p><strong>Produits en vente :</strong> <?= count($products) ?></p>
    </div>
  </div>

  <?php if (count($products) > 0): ?>
    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <a href="product.php?id=<?= e($product['id']) ?>" style="text-decoration:none;">
            <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                <img src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/600x400?text=No+Image" alt="Pas d'image">
            <?php endif; ?>
          </a>
          
          <div class="product-info">
            <h3><a href="product.php?id=<?= e($product['id']) ?>" style="color:var(--dark); text-decoration:none;"><?= e($product['name']) ?></a></h3>
            <p> <?= e($product['description']) ?></p>
            <p class="price"><?= number_format($product['price'], 2, ',', ' ') ?> €</p>
            
            <div class="actions">
                <a href="edit_product.php?id=<?= e($product['id']) ?>" class="btn edit-btn">Modifier</a>
                <form action="delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.');">
                    <input type="hidden" name="csrf_token" value="<?= ensure_csrf_token() ?>">
                    <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                    <button type="submit" class="btn delete-btn">Supprimer</button>
                </form>
            </div>
            
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>Vous n'avez pas encore de produits en vente.</p>
  <?php endif; ?>
  
  <footer class="footer">
      &copy; <?= date('Y') ?> SokoSneakers
  </footer>
</div>
</body>
</html>