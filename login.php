<?php
// login.php
require_once 'functions.php';
$token = ensure_csrf_token();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SokoSneakers - Connexion</title>
  <style>
      /* style.css - Modern & Focused Login Styling with UX improvements */
:root{
  --primary: #FF385C; /* Rouge vibrant et engageant */
  --bg: #F9FAFB; /* Gris très clair pour le fond */
  --card: #FFFFFF; /* Blanc pur pour la carte */
  --dark: #1F2937; /* Gris foncé pour le texte */
  --shadow-subtle: 0 8px 30px rgba(0, 0, 0, 0.1); /* Ombre légèrement plus prononcée */
  --border-radius: 12px;
  --error: #ef4444; /* Rouge pour les messages d'erreur */
}

/* Base */
*{box-sizing:border-box}
body{
  font-family: 'Inter', sans-serif;
  background: url('image/logo.png');
  color: var(--dark);
  margin:0;
  padding:0;
  line-height: 1.6;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh; 
}
.container{
  max-width:400px; 
  width: 100%;
  padding:0 24px;
}

/* Header */
.header{
  text-align: center; 
  margin-bottom:30px;
}
.logo { 
    font-family: 'courier','consolas',monospace;
    font-weight:800; 
    color:var(--primary); 
    font-size:32px; 
    text-decoration:none; 
    letter-spacing: -0.5px;
    text-shadow: 2px 2px #b2b2b2;
}

/* Card (Form Container) */
.card{
  background:var(--card);
  padding:35px; 
  border-radius:var(--border-radius);
  box-shadow:var(--shadow-subtle);
}
.card h2 {
    font-size: 2em;
    font-weight: 700;
    margin-top: 0;
    margin-bottom: 25px;
    color: var(--dark);
    text-align: center;
}

/* Forms */
.form-group{ 
    margin-bottom:20px; 
    position: relative; /* Nécessaire pour positionner l'icône de l'œil */
}
label {
    display: block;
    font-size: 0.9em;
    font-weight: 600;
    margin-bottom: 6px;
    color: #4B5563;
}
input[type=text], input[type=email], input[type=password], input[type=number], textarea{
  width:100%; 
  padding:12px; 
  border:1px solid #D1D5DB; 
  border-radius:8px;
  font-size: 1em;
  transition: border-color 0.3s, box-shadow 0.3s;
}
input:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 56, 92, 0.2); 
}
input.is-invalid {
    border-color: var(--error) !important;
}
.error-message {
    color: var(--error);
    font-size: 0.85em;
    margin-top: 5px;
    display: none;
}

/* Password Toggle Icon */
.password-toggle {
    position: absolute;
    right: 12px;
    top: 38px; /* Ajuster la position verticale */
    cursor: pointer;
    color: #9CA3AF;
    transition: color 0.3s;
    font-size: 1.2em; /* Pour l'icône de l'œil */
    line-height: 1; /* Assurer un alignement correct */
}
.password-toggle:hover {
    color: var(--dark);
}

/* Button */
button, .btn {
  background:var(--primary); 
  color:#fff; 
  border:none; 
  padding:12px 20px; 
  border-radius:8px; 
  cursor:pointer;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: background-color 0.3s, transform 0.1s;
  width: 100%; 
  margin-top: 15px; 
}
.ou {
    text-align: center;
    font-size: 20px;
}
.inscription {
    text-decoration: none;
    color: white;
}
button:hover, .btn:hover {
    background: #e00028; 
    transform: translateY(-1px);
}
.card-logo {
    text-align: center;
    margin-bottom: 25px;
}
.card-logo img {
    max-width: 250px; /* Ajustez la taille selon vos besoins */
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Responsive tweaks */
@media (max-width:480px){
  .container { margin: 20px auto; }
  .card { padding: 25px; }
  .logo { font-size: 28px; }
}

  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container">
  <header class="header">
    <a class="logo" href="index.php">SokoSneakers</a>
  </header>
  
  <div class="card">
    <h2>Connexion</h2>
    <form id="loginForm" action="process_login.php" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?=$token?>">
      
      <div class="form-group">
        <label for="identity">Email</label>
        <input type="text" id="identity" name="identity" placeholder=".....@gmail.com" required>
        <div id="identity-error" class="error-message">Veuillez entrer votre nom d'utilisateur ou email.</div>
      </div>
      
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required minlength="6">
        <span class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></span>
        <div id="password-error" class="error-message">Le mot de passe doit contenir au moins 6 caractères.</div>
      </div>
      
      <button type="submit">Se connecter</button>
      
      <h1 class="ou">OU</h1>
      <button><a href="process_register.php" class='inscription' >S'inscrire</a></button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const form = document.getElementById('loginForm');
    const identityInput = document.getElementById('identity');

    // ===================================
    // 1. Affichage/Masquage du Mot de Passe
    // ===================================
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function (e) {
            // Basculer le type de l'input entre 'password' et 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Basculer l'icône de l'œil
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // ===================================
    // 2. Validation Côté Client de Base
    // ===================================
    form.addEventListener('submit', function (e) {
        let formIsValid = true;

        // Validation Nom d'utilisateur/Email
        if (identityInput.value.trim() === '') {
            showError(identityInput, 'Veuillez entrer votre nom d\'utilisateur ou email.');
            formIsValid = false;
        } else {
            clearError(identityInput);
        }

        // Validation Mot de passe (longueur minimale 6)
        if (passwordInput.value.length < 6) {
            showError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères.');
            formIsValid = false;
        } else {
            clearError(passwordInput);
        }

        if (!formIsValid) {
            e.preventDefault(); // Empêche l'envoi du formulaire si la validation échoue
        }
    });

    // Écouteurs pour effacer les erreurs lors de la saisie
    identityInput.addEventListener('input', () => {
        if (identityInput.value.trim() !== '') clearError(identityInput);
    });
    passwordInput.addEventListener('input', () => {
        if (passwordInput.value.length >= 6) clearError(passwordInput);
    });
    
    // Fonctions utilitaires
    function showError(inputElement, message) {
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

});
</script>
</body>
</html>