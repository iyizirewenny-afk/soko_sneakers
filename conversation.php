<?php
// conversation.php
require_once 'functions.php';
require_login();
$user = current_user($pdo);
$token = ensure_csrf_token();

// 1. Récupérer les IDs nécessaires depuis l'URL
$product_id = (int)($_GET['product_id'] ?? 0);
$interlocutor_id = (int)($_GET['interlocutor_id'] ?? 0);

if (!$product_id || !$interlocutor_id || $user['id'] === $interlocutor_id) {
    // Rediriger si les paramètres sont invalides ou si l'utilisateur essaie de se parler à lui-même
    header('Location: inbox.php');
    exit;
}

// 2. Vérifier l'existence du produit et de l'interlocuteur
$stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$interlocutor_id]);
$interlocutor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || !$interlocutor) {
    die('Erreur: Produit ou Interlocuteur introuvable.');
}

// 3. Récupérer tous les messages de cette conversation spécifique
// La requête sélectionne les messages où l'utilisateur est l'expéditeur ET le destinataire
$stmt = $pdo->prepare("
    SELECT m.*, u_sender.username AS sender_username
    FROM messages m
    JOIN users u_sender ON m.sender_id = u_sender.id
    WHERE m.product_id = :product_id
      AND ((m.sender_id = :user_id AND m.receiver_id = :interlocutor_id)
      OR (m.sender_id = :interlocutor_id AND m.receiver_id = :user_id))
    ORDER BY m.created_at ASC
");
$stmt->execute([
    'product_id' => $product_id,
    'user_id' => $user['id'],
    'interlocutor_id' => $interlocutor_id
]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Marquer les messages reçus comme lus
$pdo->prepare("
    UPDATE messages 
    SET is_read = TRUE 
    WHERE product_id = ? AND receiver_id = ? AND sender_id = ? AND is_read = FALSE
")->execute([$product_id, $user['id'], $interlocutor_id]);

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Conversation - SokoSneakers</title>
  <style>
    /* style.css - Styles de base */
    :root{
      --primary: #FF385C; /* Couleur principale (Rouge vibrant) */
      --bg: #F9FAFB; /* Arrière-plan clair */
      --card: #FFFFFF; /* Fond de carte blanc */
      --dark: #1F2937; /* Texte sombre */
      --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08); 
      --border-radius: 12px;
      --bubble-sent: var(--primary); /* Bulle envoyée (couleur primaire) */
      --bubble-received: #E5E7EB; /* Bulle reçue (gris clair) */
    }
    *{box-sizing:border-box}
    body{font-family:'Inter', sans-serif; background:var(--bg); color:var(--dark); margin:0; padding:0; line-height:1.6;}
    .container{max-width:659px; margin:40px auto; padding:0 20px;}
    .header{display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;}
    .logo{
        font-size:34px; 
        font-weight:bold; 
        color:var(--primary); 
        text-decoration:none;
        font-family: 'courier','consolas',monospace; /* Changer la font pour le logo si possible */
        text-shadow: 2px 2px #b2b2b2;
    }
    .btn{
        display:inline-block; 
        padding:10px 18px; 
        text-decoration:none; 
        font-weight:600; 
        border:none; 
        border-radius:8px; 
        cursor:pointer; 
        transition:background-color 0.2s;
    }
    .btn.primary{
        background:var(--primary); 
        color:white;
    }
    .btn.secondary{
        background:#666;
        color:white;
    }
    .meta{
        font-size:13px; 
         color:#666;
    }
    .form-group{margin-bottom:15px;}
    textarea{width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;}

    /* Styles de Chat Spécifiques */
    .chat-box {
        background: var(--card);
        border: 1px solid #d1d5db;
        border-radius: var(--border-radius);
        padding: 20px;
        height: 500px; /* Hauteur fixe pour une meilleure UX */
        overflow-y: auto; /* Permet le défilement */
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px;
    }
    .message-row {
        display: flex;
        /* Reste le plus flexible possible par défaut */
    }
    .message-bubble {
        padding: 12px 16px;
        border-radius: 18px;
        max-width: 70%; /* Ne prend pas toute la largeur */
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    /* Message Reçu (Interlocuteur) */
    .message-row.received {
        justify-content: flex-start; /* Aligné à gauche */
    }
    .message-row.received .message-bubble {
        background: var(--bubble-received);
        color: var(--dark);
        border-bottom-left-radius: 4px; /* Petite pointe pour le chat */
    }
    
    /* Message Envoyé (Utilisateur Courant) */
    .message-row.sent {
        justify-content: flex-end; /* Aligné à droite */
    }
    .message-row.sent .message-bubble {
        background: var(--bubble-sent);
        color: white; /* Texte blanc sur fond primaire */
        border-bottom-right-radius: 4px; /* Petite pointe pour le chat */
    }

    /* Style de l'heure du message */
    .message-meta {
        margin-top: 5px;
        font-size: 11px;
        text-align: right;
        opacity: 0.8;
        /* Ajustement de la couleur pour le message envoyé */
        color: inherit; /* Utilise la couleur du parent (.message-bubble) */
    }
    .message-row.sent .message-meta {
        color: rgba(255, 255, 255, 0.9);
    }
    footer {
     margin-top: 20px;
     text-align: center;   
    }
  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav>
        <a href="inbox.php" class="btn secondary">Retour à la Messagerie</a>
        <a href="logout.php" class="btn secondary">Déconnexion</a>
    </nav>
  </header>

  <h1>
      Conversation à propos de <?= e($product['name']) ?>
  </h1>
  <p class="meta">
      Vous échangez avec <?= e($interlocutor['username']) ?>
  </p>
  <form action="delete_conversation.php" method="POST" style="margin-top: 20px;">
    <input type="hidden" name="product_id" value="<?= e($product_id) ?>">
    <input type="hidden" name="interlocutor_id" value="<?= e($interlocutor_id) ?>">
    <button type="submit" class="btn secondary" style="width: 100%;">
        Effacer la conversation
    </button>
</form>
  <div id="chat-box" class="chat-box">
    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $message): ?>
            <?php 
            // Détermine si le message a été ENVOYÉ par l'utilisateur courant
            $is_sender = $user['id'] == $message['sender_id'];
            ?>
            <div class="message-row <?= $is_sender ? 'sent' : 'received' ?>">
                <div class="message-bubble">
                    <p style="margin: 0; white-space: pre-wrap;"><?= nl2br(e($message['message'])) ?></p>
                    <p class="meta message-meta">
                        <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: #9ca3af;">
            Dites bonjour à **<?= e($interlocutor['username']) ?>** pour lancer la conversation !
        </p>
    <?php endif; ?>
  </div>
  
  <form action="process_message.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
      <input type="hidden" name="product_id" value="<?= e($product_id) ?>">
      <input type="hidden" name="receiver_id" value="<?= e($interlocutor_id) ?>">
      
      <div class="form-group">
          <textarea 
              name="message" 
              id="message" 
              rows="3" 
              placeholder="Tapez votre réponse ici..." 
              required 
              style="resize: vertical;"
          ></textarea>
      </div>
      
      <button type="submit" class="btn primary" style="width: 100%;">
          Envoyer le message
      </button>
  </form>

  <footer class="footer">
      &copy; <?= date('Y') ?> SokoSneakers
  </footer>
</div>
<script>
    // Faire défiler automatiquement vers le bas du chat lors du chargement
    const chatBox = document.getElementById('chat-box');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
</body>
</html>