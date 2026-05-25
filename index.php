<?php
// index.php
require_once 'config.php';
require_once 'functions.php'; 
$user = current_user($pdo); 
$stmt = $pdo->query("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// La fonction is_admin est redondante si functions.php est inclus, 
// mais on la laisse si elle est utilisée ailleurs (on préfère is_admin_user($user)).
// function is_admin($user) {
//     return isset($user['is_admin']) && $user['is_admin'] === 1;
// }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SokoSneakers - Accueil</title>
  <style>
      /* style.css - Modern, Clean & Sophisticated Styling */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
:root{
  --primary: #FF385C; /* Rouge vibrant moderne */
  --secondary: #0077b6; /* Bleu d'accentuation */
  --bg: #F9FAFB; /* Gris très clair, presque blanc */
  --card: #FFFFFF;
  --dark: #1F2937; /* Gris foncé pour le texte */
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08); /* Ombre douce */
  --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.1); 
  --border-radius: 12px;
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
  max-width:1200px; 
  margin:40px auto; 
  padding:0 20px;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    padding: 15px 0;
}
.logo { 
    font-weight:700; 
    color:var(--primary); 
    font-size:28px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
    font-family:'Berlin Sans FB Demi',cursive;
    text-shadow: 2px 2px #b2b2b2;
}
.nav-link {
    text-decoration: none;
    color: var(--dark);
    font-weight: 600;
    margin-left: 20px;
    transition: color 0.2s;
}
.nav-link:hover {
    color: var(--primary);
}
.btn{
    display:inline-block;
    padding:10px 18px;
    text-decoration:none;
    font-weight:700;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:background-color 0.2s;
    background:var(--primary);
    color:white;
    margin-left: 20px;
}
.btn:hover{
    background: #E0354F;
}

/* --- NOUVEAUX STYLES DE NOTIFICATION --- */
.nav-item {
    position: relative;
    display: inline-block;
    margin-left: 20px;
    text-decoration: none;
    color: var(--dark);
    font-weight: 600;
    transition: color 0.2s;
    padding: 5px 0; /* Ajouté pour le badge */
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -15px; 
    background-color: var(--primary); 
    color: white;
    border-radius: 50%;
    padding: 3px 6px; 
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    min-width: 18px; 
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}
.product-card {
    background: var(--card);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-light);
    transition: transform 0.3s, box-shadow 0.3s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}
.product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}
.product-info {
    padding: 15px;
}
.product-info h3 {
    margin-top: 0;
    margin-bottom: 5px;
    font-size: 18px;
}
.product-info p {
    margin: 5px 0;
    color: var(--text-subtle);
}
.price {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 10px !important;
}

/* Search bar styling */
.search-container {
    display: flex;
    max-width: 600px;
    margin: 20px auto 40px;
    box-shadow: var(--shadow-light);
    border-radius: 8px;
    overflow: hidden;
}
.search-container input[type="text"] {
    flex-grow: 1;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-right: none;
    font-size: 16px;
    outline: none;
}
.search-container button {
    background: var(--primary);
    color: white;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}
.search-container button:hover {
    background: #E0354F;
}

/* Helper styles for search highlighting */
mark {
    background-color: yellow;
    color: black;
    padding: 0;
}
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav>
      <a href="index.php" class="nav-link">Accueil</a>
      <a href="product.php" class="nav-link">Produits</a>
      
      <?php if ($user): 
            $unread_count = get_unread_message_count($pdo, $user['id']); // Appel de la fonction
      ?>
          <?php if (is_admin_user($user)): ?>
              <a href="admin.php" class="btn" style="background: var(--secondary);">Admin 👑</a>
          <?php endif; ?>
          
          <a href="seller_account.php" class="nav-link">Mon Compte</a>
          
          <a href="inbox.php" class="nav-item">
              Messagerie
              <?php if ($unread_count > 0): ?>
                  <span class="notification-badge"><?= e($unread_count) ?></span>
              <?php endif; ?>
          </a>

          <a href="logout.php" class="btn">Déconnexion</a>
      <?php else: ?>
          <a href="login.php" class="nav-link">Connexion</a>
          <a href="register.php" class="btn">Inscription</a>
      <?php endif; ?>
    </nav>
  </header>

  <div class="search-container">
    <input type="text" id="searchInput" placeholder="Rechercher des produits ou du contenu...">
    <button onclick="searchText()">Rechercher</button>
  </div>
  
  <?php if (isset($_SESSION['message'])): ?>
      <div class="alert success-message" style="background:#d1fae5; color:#10B981; padding:10px; border-radius:8px; margin-bottom:20px;">
          <?= htmlspecialchars($_SESSION['message']) ?>
      </div>
      <?php unset($_SESSION['message']); ?>
  <?php endif; ?>
  <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert error-message" style="background:#fee2e2; color:#ef4444; padding:10px; border-radius:8px; margin-bottom:20px;">
          <?= htmlspecialchars($_SESSION['error_message']) ?>
      </div>
      <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>

  <h1 style="text-align:center; color: var(--dark); font-size: 32px; margin-bottom: 40px;">Découvrez nos dernières sneakers</h1>

  <?php if (!empty($products)): ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <a href="product.php?id=<?= e($product['id']) ?>">
            <?php 
            $image_src = 'uploads/' . e($product['image']);
            if (!empty($product['image']) && file_exists(UPLOAD_DIR . $product['image'])): ?>
                <img src="<?= $image_src ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/600x400?text=No+Image" alt="Pas d'image">
            <?php endif; ?>
          </a>
          <div class="product-info">
            <h3><a href="product.php?id=<?= e($product['id']) ?>" style="color:var(--dark); text-decoration:none;"><?= e($product['name']) ?></a></h3>
            <p style="font-size: 14px; color: var(--text-subtle);">Vendeur : <?= e($product['username']) ?></p>
            <p class="price"><?= number_format($product['price'], 0, ',', ' ') ?> Fbu</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="text-align: center;">Aucun produit n'est disponible pour le moment.</p>
  <?php endif; ?>

</div>

  <script>
    // Le script de recherche (searchText1 et searchText) reste inchangé
    function searchText1() {
        // ... (votre code de recherche existant)
        const input = document.getElementById("searchInput").value.toLowerCase();
        
        // Supprimer toutes les anciennes marques
        document.querySelectorAll('mark').forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize(); // Fusionner les noeuds texte
        });
        
        if (!input) return;

        // Créer un TreeWalker pour parcourir le texte sans altérer la structure HTML
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        let firstMatch = null;

        while (walker.nextNode()) {
            const node = walker.currentNode;
            const parent = node.parentNode;

            // Ignorer le contenu des balises de script et de style
            if (parent.nodeName === 'SCRIPT' || parent.nodeName === 'STYLE') {
                continue;
            }

            if (!node.nodeValue.toLowerCase().includes(input.toLowerCase()))
            continue;

            const regex = new RegExp(`(${input})`,'gi');
            const highlightedHTML = node.nodeValue.replace(regex, '<mark>$1</mark>');
            const temp = document.createElement("span");
            temp.innerHTML = highlightedHTML;

            parent.replaceChild(temp, node);

            if (!firstMatch) {
                firstMatch = temp.querySelector('mark');
            }
        }

        // defilement fluide vers le premier mot trouve
        if (firstMatch) {
            firstMatch.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        } else {
            alert("Mot non trouvé... ");
        }
    }
    function searchText() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const bodyText = document.body.innerText.toLowerCase();

        if (bodyText.includes(input)) {
            searchText1();
        } else {
            alert("Mot non trouvé...");
        }
    }
  </script>
</body>
</html>