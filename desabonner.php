<?php
session_start();
$host = 'localhost';
$dbname = 'finaldb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion.");
}

if (!isset($_SESSION['user_id'])) {
    header('Location: create_account.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $abonnement_id = $_POST['abonnement_id'] ?? 0;

    if (is_numeric($abonnement_id)) {
        $stmt = $pdo->prepare("DELETE FROM abonnement WHERE id = :id AND utilisateur_id = :user_id");
        $stmt->execute(['id' => $abonnement_id, 'user_id' => $_SESSION['user_id']]);
    }

    header('Location: Acceuil.php#abonnements');
    
}
?>