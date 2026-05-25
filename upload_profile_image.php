<?php
// upload_profile_image.php
require_once 'functions.php';
require_login(); 

$user = current_user($pdo);
$token = ensure_csrf_token();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalide.';
    } else {
        $file = $_FILES['profile_image'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5 Mo

            if ($file['size'] > $max_size) {
                $error = 'Le fichier est trop volumineux (max. 5 Mo).';
            } elseif (!in_array($file['type'], $allowed_types)) {
                $error = 'Type de fichier non supporté. Utilisez JPG, JPEG ou PNG.';
            } else {
                // 1. Définir un nom de fichier unique et sécurisé (ex: user_ID_timestamp.ext)
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_file_name = $user['id'] . '_' . time() . '.' . strtolower($ext);
                $destination = UPLOAD_DIR . $new_file_name;

                // 2. Déplacer le fichier téléchargé
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    
                    // 3. (Optionnel) Supprimer l'ancienne image si elle existe
                    if (!empty($user['profile_image'])) {
                        $old_path = UPLOAD_DIR . $user['profile_image'];
                        if (file_exists($old_path)) {
                             // Supprimer l'ancien fichier pour éviter d'encombrer le serveur
                            unlink($old_path); 
                        }
                    }

                    // 4. Mettre à jour la base de données
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$new_file_name, $user['id']]);

                    $message = 'Votre photo de profil a été mise à jour avec succès !';
                    // Recharger les données utilisateur avec la nouvelle image
                    $user = current_user($pdo);

                } else {
                    $error = 'Erreur lors du déplacement du fichier.';
                }
            }
        } else {
            // Gérer le cas où aucun fichier n'a été sélectionné ou autre erreur d'upload
            if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                 $error = 'Erreur d\'upload : Code ' . $file['error'];
            }
        }
    }
}

// Définir la source de l'image pour l'affichage
$profile_image_src = 'uploads/' . e($user['profile_image'] ?? '');
if (empty($user['profile_image']) || !file_exists(UPLOAD_DIR . ($user['profile_image'] ?? ''))) {
    $initial = strtoupper(substr(e($user['username']), 0, 1));
    $profile_image_src = 'https://via.placeholder.com/100?text=' . $initial;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Mettre à jour la Photo de Profil</title>
  <style>
/* Style minimaliste pour le formulaire */
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
*{box-sizing:border-box}
body{
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--dark);
  margin:0;
  padding:0;
  line-height: 1.6;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
.form-card{
    max-width: 500px;
    width: 90%;
    background: var(--card);
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
    text-align: center;
}
h1{ color: var(--primary); margin-top: 0; font-size: 24px; }

.profile-preview {
    margin-bottom: 20px;
}
.profile-image {
    width: 150px; 
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--primary);
    box-shadow: var(--shadow-light);
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}
label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background: #f7f7f7;
}

.btn{
    display:block;
    width:100%;
    padding:12px;
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
  </style>
</head>
<body>
<div class="form-card">
    <h1>Modifier votre Photo de Profil</h1>

    <?php if ($error): ?>
        <div class="error-message"><?= e($error) ?></div>
    <?php elseif ($message): ?>
        <div class="success-message"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="profile-preview">
        <img src="<?= $profile_image_src ?>" alt="Aperçu du Profil" class="profile-image">
    </div>

    <form action="upload_profile_image.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
        
        <div class="form-group">
            <label for="profile_image">Sélectionner une nouvelle image (JPG ou PNG, max 5Mo)</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png" required>
        </div>
        
        <button type="submit" class="btn">Télécharger et Enregistrer</button>
    </form>
    
    <p style="margin-top: 20px;">
        <a href="seller_account.php" style="color: var(--dark); text-decoration: none;">&larr; Retour à l'Espace Vendeur</a>
    </p>

</div>
</body>
</html>