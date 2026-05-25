<?php
// admin.php
require_once 'functions.php';
// Vérifie que l'utilisateur est connecté ET est un administrateur.
require_admin($pdo); 

$user = current_user($pdo);

// Récupérer la liste de TOUS les utilisateurs
$stmt_users = $pdo->query("SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC");
$all_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste de TOUS les produits
// Joindre la table products avec la table users pour obtenir le nom du vendeur
$stmt_products = $pdo->query("SELECT p.id, p.name, p.price, u.username AS seller_username FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$all_products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Tableau de Bord Admin - SokoSneakers</title>
  <style>
      /* style.css - SokoSneakers Admin Styling */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

:root{
  --primary: #3498db; /* Bleu élégant pour l'Admin */
  --secondary: #2c3e50; /* Gris foncé pour l'accentuation */
  --bg: #F8F9FA; /* Fond très clair */
  --card: #FFFFFF; /* Blanc pur pour les cartes */
  --dark: #1F2937; /* Texte */
  --text-subtle: #6B7280; /* Texte secondaire */
  --border: #E5E7EB; /* Lignes de séparation subtiles */
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.05); 
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
  max-width:1400px; /* Un peu plus large pour le dashboard */
  margin:30px auto;
  padding:0 30px;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    padding: 15px 0;
    border-bottom: 2px solid var(--border);
}
.logo { 
    font-weight:700; 
    color:var(--primary); 
    font-size:32px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
}
.nav-link {
    text-decoration: none;
    color: var(--text-subtle);
    font-weight: 600;
    margin-left: 20px;
    transition: color 0.2s;
}
.nav-link:hover {
    color: var(--primary);
}
.admin-btn {
    background: var(--primary);
    color: white !important;
    padding: 8px 15px;
    border-radius: 8px;
    margin-left: 20px;
}
.admin-btn:hover {
    background: #2980b9;
}

/* Dashboard Grid Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr; /* Deux colonnes égales */
    gap: 30px;
    margin-top: 30px;
}
@media (max-width: 1000px) {
    .dashboard-grid {
        grid-template-columns: 1fr; /* Une seule colonne sur mobile */
    }
}

/* Admin Card Style */
.admin-card {
    background: var(--card);
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
    border: 1px solid var(--border);
}
.admin-card h2 {
    color: var(--secondary);
    font-size: 22px;
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
}

/* Table Style */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}
.data-table th, .data-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.data-table th {
    background-color: #f7f7f7;
    color: var(--text-subtle);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
}
.data-table tbody tr:hover {
    background-color: #fcfcfc;
}
.data-table td a {
    text-decoration: none;
    transition: color 0.2s;
}
.data-table td a:hover {
    text-decoration: underline;
}
.admin-status {
    font-weight: 600;
}
.admin-status.admin {
    color: #27ae60; /* Vert pour Admin */
}
.admin-status.standard {
    color: var(--text-subtle); /* Gris pour Standard */
}

/* Styles pour les actions */
.action-link {
    margin-right: 8px;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
}
.action-edit {
    color: #2980b9;
}
.action-delete {
    color: #e74c3c;
}
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">Admin Dashboard</a>
    <nav>
      <a href="seller_account.php" class="nav-link">Espace Vendeur</a>
      <a href="index.php" class="nav-link">Voir le Site</a>
      <a href="logout.php" class="nav-link admin-btn" style="background: var(--secondary); margin-left: 30px;">Déconnexion</a>
    </nav>
  </header>

  <h1 style="color: var(--dark); font-size: 36px; margin-bottom: 30px;">
      Tableau de Bord Administratif
  </h1>
  
  <div class="dashboard-grid">
      <div class="admin-card">
        <h2>Utilisateurs Enregistrés (<?= count($all_users) ?>)</h2>
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom d'Utilisateur</th>
              <th>Email</th>
              <th>Statut</th>
              <th>Inscrit le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_users as $u): ?>
              <tr>
                <td><?= e($u['id']) ?></td>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td class="admin-status <?= $u['is_admin'] ? 'admin' : 'standard' ?>">
                    <?= $u['is_admin'] ? 'Admin 👑' : 'Standard' ?>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <a href="edit_user.php?id=<?= e($u['id']) ?>" class="action-link action-edit">Modifier</a> | 
                  <a href="delete_user.php?id=<?= e($u['id']) ?>" 
                     class="action-link action-delete" 
                     onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Tous ses produits seront aussi supprimés.');">Supprimer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="admin-card">
        <h2>Liste des Produits (<?= count($all_products) ?>)</h2>
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom du Produit</th>
              <th>Prix (Fbu)</th>
              <th>Vendeur</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_products as $p): ?>
              <tr>
                <td><?= e($p['id']) ?></td>
                <td><a href="product.php?id=<?= e($p['id']) ?>" style="color: var(--dark);"><?= e($p['name']) ?></a></td>
                <td><?= number_format($p['price'], 0, ',', ' ') ?> Fbu</td>
                <td><?= e($p['seller_username']) ?></td>
                <td>
                  <a href="edit_product.php?id=<?= e($p['id']) ?>" class="action-link action-edit">Modifier</a> | 
                  <a href="delete_product.php?id=<?= e($p['id']) ?>" 
                     class="action-link action-delete"
                     onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
  </div>
</div>
</body>
</html>