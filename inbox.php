<?php
// inbox.php
require_once 'functions.php';
require_login();

$user = current_user($pdo);
$user_id = $user['id'];

// Requête SQL avancée pour récupérer le DERNIER message de CHAQUE fil de conversation unique
$stmt = $pdo->prepare("
    SELECT 
        m1.*, 
        p.name AS product_name,
        -- Détermine l'ID de l'interlocuteur (l'autre personne dans le chat)
        CASE 
            WHEN m1.sender_id = :user_id THEN m1.receiver_id 
            ELSE m1.sender_id 
        END AS interlocutor_id,
        u_interlocutor.username AS interlocutor_username,
        -- Vérifie si l'utilisateur courant a des messages NON LUS dans ce fil de la part de l'interlocuteur
        (
            SELECT COUNT(*) 
            FROM messages m2
            WHERE m2.product_id = m1.product_id
              AND m2.receiver_id = :user_id 
              AND m2.sender_id = (CASE WHEN m1.sender_id = :user_id THEN m1.receiver_id ELSE m1.sender_id END)
              AND m2.is_read = FALSE
        ) > 0 AS has_unread
    FROM messages m1
    JOIN products p ON m1.product_id = p.id
    -- Jointure pour obtenir le nom d'utilisateur de l'interlocuteur
    JOIN users u_interlocutor ON u_interlocutor.id = (
        CASE 
            WHEN m1.sender_id = :user_id THEN m1.receiver_id 
            ELSE m1.sender_id 
        END
    )
    WHERE m1.id IN (
        -- Sous-requête pour trouver l'ID du message le plus récent (MAX(id) est utilisé comme proxy pour le plus récent)
        SELECT MAX(id)
        FROM messages m_latest
        WHERE m_latest.sender_id = :user_id OR m_latest.receiver_id = :user_id
        -- Groupement par produit et par les deux utilisateurs, triés pour être constants (LEAST/GREATEST)
        GROUP BY product_id, LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
    )
    ORDER BY m1.created_at DESC
");
// Exécute la requête en liant l'ID de l'utilisateur pour tous les placeholders
$stmt->execute([
    'user_id' => $user_id
]);

$threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Ma Messagerie - SokoSneakers</title>
  <style>
    /* style.css - (Ajoutez ici vos styles ou assurez-vous qu'ils sont inclus) */
    :root{
      --primary: #FF385C; 
      --bg: #F9FAFB;
      --card: #FFFFFF;
      --dark: #1F2937;
      --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
      --border-radius: 12px;
      --border-subtle: #e5e7eb;
    }
    *{box-sizing:border-box}
    body{font-family:'Inter', sans-serif; background:var(--bg); color:var(--dark); margin:0; padding:0; line-height:1.6;}
    .container{max-width:800px; margin:40px auto; padding:0 20px;}
    .header{display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;}
    .logo { 
   
    font-weight:800; 
    color:var(--primary); 
    font-size:38px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
    font-family:'Brush Script MT',cursive;
    text-shadow: 2px 2px #b2b2b2;
    }
    .btn{display:inline-block; padding:10px 18px; text-decoration:none; font-weight:600; border:none; border-radius:8px; cursor:pointer; transition:background-color 0.2s;}
    .btn.primary{background:var(--primary); color:white;}
    .btn.secondary{background:#666; color:white;}
    .meta{font-size:13px; color:#666;}

    .message-card {
        background: var(--card);
        padding: 20px;
        margin-bottom: 15px;
        border-radius: var(--border-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-subtle);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .message-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .unread {
        border-left: 5px solid var(--primary); /* Met en évidence les messages non lus */
        background: #fffafa; /* Fond légèrement coloré pour l'attention */
    }
    .unread .meta { font-weight: bold; color: var(--primary); }
    footer {
        text-align: center;
        margin-top: 20px;
    }
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav>
        <a href="add_product.php" class="btn">Ajouter un produit</a>
        <a href="logout.php" class="btn secondary">Déconnexion</a>
    </nav>
  </header>

  <h1>💌 Ma Messagerie</h1>
  
  <?php if (count($threads) > 0): ?>
    <?php foreach ($threads as $thread): ?>
        <?php 
        // L'interlocuteur est l'autre personne dans le fil
        $interlocutor_id = $thread['interlocutor_id'];
        $conversation_link = "conversation.php?product_id=" . e($thread['product_id']) . "&interlocutor_id=" . e($interlocutor_id);
        $is_sender = $thread['sender_id'] == $user_id;
        $status_text = $is_sender ? 'Envoyé' : 'Reçu';
        $read_status = $thread['has_unread'] ? 'Nouvelle réponse !' : 'Ouvrir la conversation ➡️';
        ?>

        <a href="<?= $conversation_link ?>" style="text-decoration: none; color: inherit;">
            <div class="message-card <?= $thread['has_unread'] ? 'unread' : '' ?>">
                <h3>SUJET : <?= e($thread['product_name']) ?></h3>
                <p class="meta">
                    Avec : <?= e($thread['interlocutor_username']) ?> - Dernier message <?= $status_text ?> le : <?= date('d/m/Y H:i', strtotime($thread['created_at'])) ?>
                </p>
                <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 10px 0;">
                
                <p style="color: var(--dark); margin: 0; font-style: italic;">
                    "<?= nl2br(substr(e($thread['message']), 0, 80)) ?>..."
                </p> 
                
                <p style="margin-top: 15px; text-align: right; font-weight: 600; color: <?= $thread['has_unread'] ? 'var(--primary)' : 'var(--dark)' ?>;">
                    <?= $read_status ?>
                </p>
                
            </div>
        </a>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-light); margin: 0;">Votre boîte de réception est vide.</p>
    </div>
<?php endif; ?>

  <footer class="footer">
    &copy; <?= date('Y') ?> SokoSneakers
  </footer>
</div>
</body>
</html>