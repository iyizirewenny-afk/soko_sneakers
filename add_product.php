<?php
// add_product.php
require_once 'functions.php';
require_login();
$token = ensure_csrf_token();
$user = current_user($pdo);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Ajouter Produit - SokoSneakers</title>
  <style>
      /* style.css - Modern Form Styling */
:root{
  --primary: #FF385C; /* Rouge vibrant moderne */
  --bg: #F9FAFB; /* Gris très clair, presque blanc */
  --card: #FFFFFF;
  --dark: #1F2937; /* Gris foncé pour le texte */
  --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08); /* Ombre douce */
  --border-radius: 12px;
  --error: #ef4444; /* Rouge pour les messages d'erreur */
}

/* Base */
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
  max-width:800px; /* Taille optimisée pour le formulaire */
  margin:40px auto; 
  padding:0 24px;
}

/* Header & Navigation */
.header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding: 15px 0; 
  border-bottom: 1px solid #E5E7EB; 
  margin-bottom:30px;
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
.nav span {
    margin-right: 15px;
    font-size: 0.95em;
    color: #4B5563;
}
mark {
    background-color: antiquewhite;
}
.nav a { 
    text-decoration:none; 
    color:var(--dark); 
    font-weight: 500;
}

/* Card */
.card{
  background:var(--card);
  padding:35px; /* Padding généreux */
  border-radius:var(--border-radius);
  box-shadow:var(--shadow-light);
  margin-bottom:25px;
}
.card h2 {
    font-size: 2em;
    font-weight: 700;
    margin-top: 0;
    margin-bottom: 30px;
    color: var(--dark);
    border-bottom: 2px solid var(--primary); 
    display: inline-block;
    padding-bottom: 5px;
}

/* Forms */
.form-group{ margin-bottom:25px; }
label {
    display: block;
    font-size: 1em;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--dark);
}
input[type=text], input[type=email], input[type=password], input[type=number], textarea{
  width:100%; 
  padding:12px; 
  border:1px solid #D1D5DB; 
  border-radius:8px;
  font-size: 1em;
  transition: border-color 0.3s, box-shadow 0.3s;
}
input[type=file] {
    padding: 10px 0; /* Padding personnalisé pour les fichiers */
}
input:focus, textarea:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 56, 92, 0.2);
}
/* Input error style */
input.is-invalid, textarea.is-invalid {
    border-color: var(--error) !important;
}
.error-message {
    color: var(--error);
    font-size: 0.85em;
    margin-top: 5px;
    display: none;
}

/* Image Preview */
#image-preview {
    margin-top: 15px;
    max-width: 250px;
    height: auto;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    display: none; /* Caché par défaut */
    object-fit: cover;
    box-shadow: var(--shadow-light);
}


/* Button */
button, .btn {
  background:var(--primary); 
  color:#fff; 
  border:none; 
  padding:14px 25px; /* Bouton plus grand et accrocheur */
  border-radius:var(--border-radius); 
  cursor:pointer;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: background-color 0.3s, transform 0.1s;
  text-decoration: none; 
  display: inline-block; 
}
button:hover, .btn:hover {
    background: #e00028; 
    transform: translateY(-1px);
}
.btn.secondary { background:#4B5563; }
.btn.secondary:hover { background:#374151; }

/* Small Text */
.meta { font-size:13px; color:#6B7280; }

/* Footer */
.footer { 
    text-align:center; 
    color:#9CA3AF; 
    margin:40px 0 20px; 
    font-size:14px; 
    padding-top: 15px;
    border-top: 1px solid #E5E7EB;
}

/* Responsive tweaks */
@media (max-width:600px){
  .container { margin: 20px auto; padding: 0 16px; }
  .header { flex-direction:column; align-items:flex-start; gap:15px; margin-bottom: 20px; }
  .nav { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
}

  </style>
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
    <nav class="nav">
        <span>Connecté <mark><?=e($user['username'])?></mark></span>
      <a href="logout.php" class="btn secondary">Déconnexion</a>
    </nav>
  </header>

  <div class="card">
    <h2>Ajouter un nouveau produit</h2>
    <form id="addProductForm" action="submit_product.php" method="POST" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf_token" value="<?=$token?>">
      
      <div class="form-group">
        <label for="name">Nom de la sneaker *</label>
        <input type="text" id="name" name="name" required>
        <div id="name-error" class="error-message">Le nom de la sneaker est requis.</div>
      </div>
      
      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"></textarea>
      </div>
      
      <div class="form-group">
        <label for="price">Prix (Fbu) *</label>
        <input type="number" step="0.01" id="price" name="price" required min="0.01">
        <div id="price-error" class="error-message">Veuillez entrer un prix valide (minimum 100Fbu).</div>
      </div>
      
      <div class="form-group">
        <label for="image">Image du produit (jpg/png) *</label>
        <input type="file" id="image" name="image" accept="image/png, image/jpeg" required>
        <img id="image-preview" src="#" alt="Aperçu de l'image">
        <div id="image-error" class="error-message">Veuillez sélectionner une image (JPG ou PNG).</div>
      </div>
      
      <button type="submit">Ajouter le produit</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('addProductForm');
    const nameInput = document.getElementById('name');
    const priceInput = document.getElementById('price');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');

    // ===================================
    // 1. Aperçu de l'Image (Image Preview)
    // ===================================
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            
            reader.readAsDataURL(this.files[0]);
            clearError(imageInput); // Effacer l'erreur s'il y en a une
        } else {
            imagePreview.src = '#';
            imagePreview.style.display = 'none';
        }
    });

    // ===================================
    // 2. Validation Côté Client
    // ===================================
    form.addEventListener('submit', function (e) {
        let formIsValid = true;

        // Validation Nom (requis)
        if (nameInput.value.trim() === '') {
            showError(nameInput, 'Le nom de la sneaker est requis.');
            formIsValid = false;
        } else {
            clearError(nameInput);
        }

        // Validation Prix (requis et positif)
        const price = parseFloat(priceInput.value);
        if (isNaN(price) || price <= 0) {
            showError(priceInput, 'Veuillez entrer un prix valide (minimum 0.01 €).');
            formIsValid = false;
        } else {
            clearError(priceInput);
        }

        // Validation Image (requis)
        if (imageInput.files.length === 0) {
             showError(imageInput, 'Veuillez sélectionner une image (JPG ou PNG).');
             formIsValid = false;
        } else {
             // Validation du type de fichier (basique, la vérification serveur est essentielle)
             const file = imageInput.files[0];
             const acceptedTypes = ['image/jpeg', 'image/png'];
             if (!acceptedTypes.includes(file.type)) {
                 showError(imageInput, 'Type de fichier non supporté. Utilisez JPG ou PNG.');
                 formIsValid = false;
             } else {
                 clearError(imageInput);
             }
        }


        if (!formIsValid) {
            e.preventDefault(); // Empêche l'envoi du formulaire si la validation échoue
            // Scroll vers le premier champ en erreur (bonne pratique UX)
            form.querySelector('.is-invalid').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Fonctions utilitaires pour afficher/effacer les erreurs
    function showError(inputElement, message) {
        // Le div d'erreur a l'id de l'input + '-error'
        const errorDiv = document.getElementById(inputElement.id + '-error');
        inputElement.classList.add('is-invalid');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function clearError(inputElement) {
        const errorDiv = document.getElementById(inputElement.id + '-error');
        inputElement.classList.remove('is-invalid');
        errorDiv.style.display = 'none';
    }

    // Effacer les erreurs lors de la saisie
    nameInput.addEventListener('input', () => { if (nameInput.value.trim() !== '') clearError(nameInput); });
    priceInput.addEventListener('input', () => { 
        const price = parseFloat(priceInput.value);
        if (!isNaN(price) && price > 0) clearError(priceInput);
    });

});
</script>
</body>
</html>