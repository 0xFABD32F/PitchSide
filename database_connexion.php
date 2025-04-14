<?php
// Informations de connexion (à personnaliser)
define('DB_HOST', 'localhost');        // Adresse du serveur MySQL
define('DB_NAME', 'finaldb'); // Nom de la base de données
define('DB_USER', 'root');     // Nom d'utilisateur
define('DB_PASS', '');    // Mot de passe

try {
    // Connexion PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
