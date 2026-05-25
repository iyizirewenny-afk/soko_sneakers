<?php
$password = 'wonder123'; // *** REMPLACEZ CECI ***
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mot de passe à hacher : " . $password . "\n";
echo "HASH GÉNÉRÉ : " . $hash . "\n";
?>