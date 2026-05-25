<?php
// product.php
require_once 'config.php';
require_once 'functions.php'; // Inclus pour utiliser current_user() et e()

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Récupère les détails du produit et le nom d'utilisateur du vendeur
$stmt = $pdo->prepare("SELECT p.*, u.username, u.id AS seller_id FROM products p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Produit introuvable.');
}

// Logique pour gérer le bouton de contact
$user = current_user($pdo); // Récupère l'utilisateur connecté (null si déconnecté)
$is_logged_in = !empty($user);
$is_seller = $is_logged_in && $user['id'] == $product['seller_id'];

// L'ID de l'interlocuteur est l'ID du vendeur
$seller_id = $product['seller_id']; 
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?=e($product['name'])?> - SokoSneakers</title>
  <style>
      /* style.css - simple responsive styling */
:root{
  --primary: #FF385C; /* Mis à jour pour cohérence */
  --bg: #F9FAFB;
  --card: #fff;
  --dark: #1F2937;
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
  --border-radius: 12px;
}

*{box-sizing:border-box}
body{
  font-family: Inter, Arial, sans-serif;
  background: var(--bg);
  color: var(--dark);
  margin:0;
  padding:0;
}
.container{
  max-width:1000px;
  margin:30px auto;
  padding:0 20px;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.logo { 
   
    font-weight:800; 
    color:var(--primary); 
    font-size:28px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
    font-family:'Brush Script MT',cursive;
    text-shadow: 2px 2px #b2b2b2;
}
.card {
    background: var(--card);
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
}
h1, h2 { color: var(--dark); }
.btn {
  display:inline-block; 
  padding:12px 25px; 
  text-decoration:none; 
  font-weight:600; 
  border:none; 
  border-radius:8px; 
  cursor:pointer;
  transition: background-color 0.2s;
}
.btn.primary { 
    background:var(--primary); 
    color:white; 
}
.btn.primary:hover {
    background: #e02f52; /* Rouge légèrement plus sombre */
}
.btn.secondary { 
    background:#666; 
    color: white;
}
.btn.secondary:hover {
    background: #555;
}

/* small */
.meta { font-size:13px; color:#666; }

.footer { text-align:center; color:#666; margin:24px 0; font-size:14px; }

/* responsive tweaks */
@media (max-width:480px){
  .header { flex-direction:column; align-items:flex-start; gap:12px; }
  .card { padding: 20px; }
}
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav>
        <?php if ($is_logged_in): ?>
            <a href="inbox.php" class="btn secondary">Messagerie</a>
            <a href="logout.php" class="btn secondary">Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="btn secondary">Connexion</a>
        <?php endif; ?>
    </nav>
  </header>

  <div class="card">
    <div style="display:flex; gap:20px; flex-wrap:wrap;">
      <div style="flex:1; min-width:260px;">
        <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
          <img src="uploads/<?=e($product['image'])?>" alt="<?=e($product['name'])?>" style="width:100%; border-radius:8px;">
        <?php else: ?>
          <img src="https://via.placeholder.com/600x400?text=No+Image" style="width:100%; border-radius:8px;">
        <?php endif; ?>
      </div>
      <div style="flex:2; min-width:300px;">
        <h1><?=e($product['name'])?></h1>
        <p class="meta">Vendu par: <?=e($product['username'])?></p>
        
        <p style="font-size:28px; color:var(--primary); font-weight:700; margin: 10px 0;">
          <?=number_format($product['price'])?> Fbu
        </p>

        <?php if (!$is_seller): ?>
            <div style="margin: 30px 0;">
                <?php if ($is_logged_in): ?>
                    <?php 
                        // Lien vers la conversation: product_id et interlocutor_id (le vendeur)
                        $conversation_link = "conversation.php?product_id=" . e($product['id']) . "&interlocutor_id=" . e($seller_id);
                    ?>
                    <a href="<?= $conversation_link ?>" class="btn primary" style="display:block; text-align:center;">
                        💬 Contacter le Vendeur (<?= e($product['username']) ?>)
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn primary" style="display:block; text-align:center;">
                        Connectez-vous pour contacter le vendeur
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p style="padding: 10px; border: 1px solid var(--primary); border-radius: 8px; background: #fff5f5; color: var(--dark); margin: 30px 0;">
                Vous êtes le vendeur de ce produit.
            </p>
        <?php endif; ?>
        <h2>Détails du produit</h2>
        <p><?=nl2br(e($product['description']))?></p>
        
        <p class="meta" style="margin-top: 20px;">
            Ajouté le : <?= date('d/m/Y', strtotime($product['created_at'])) ?>
        </p>
      </div>
    </div>
  </div>

  <footer class="footer">
    &copy; <?= date('Y') ?> SokoSneakers
  </footer>
</div>
</body>
</html>