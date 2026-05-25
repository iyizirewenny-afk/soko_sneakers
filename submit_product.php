<?php
// submit_product.php
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_product.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide');
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? '';

if (!$name || !$price) {
    die('Nom et prix requis.');
}

// Validate price
if (!is_numeric($price) || $price < 0) {
    die('Prix invalide.');
}

// Handle file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Erreur lors de l\'upload de l\'image.');
}

$file = $_FILES['image'];
$allowed_mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!array_key_exists($mime, $allowed_mimes)) {
    die('Format d\'image non supporté. Seuls JPG et PNG sont autorisés.');
}

// file size limit 2MB
if ($file['size'] > 2 * 1024 * 1024) {
    die('Fichier trop volumineux (max 2MB).');
}

// create uploads dir if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// generate unique name
$ext = $allowed_mimes[$mime];
$filename = bin2hex(random_bytes(8)) . '.' . $ext;
$dest = UPLOAD_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    die('Impossible de sauvegarder l\'image.');
}

// insert product
$stmt = $pdo->prepare("INSERT INTO products (user_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $name, $description, $price, $filename]);

header('Location: index.php');
exit;
