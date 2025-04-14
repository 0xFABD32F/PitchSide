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
    $type = $_POST['type'] ?? '';
    $reference_id = $_POST['reference_id'] ?? 0;

    if (!in_array($type, ['match', 'equipe', 'tournoi']) || !is_numeric($reference_id)) {
        header('Location: Acceuil.php#abonnements');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO abonnement (utilisateur_id, type, reference_id) VALUES (:user_id, :type, :reference_id)");
    $stmt->execute(['user_id' => $_SESSION['user_id'], 'type' => $type, 'reference_id' => $reference_id]);

    header('Location: Acceuil.php#abonnements');
    exit;
}
?>