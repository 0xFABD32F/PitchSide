<?php
require_once '../database_connexion.php';

// Vérifier si les données du formulaire sont envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $grade = trim($_POST["grade"] ?? "");

    // Validation simple
    if (empty($nom) || empty($grade)) {
        die("Tous les champs sont requis.");
    }

    try {
        // Préparer la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO arbitres (nom, grade) VALUES (:nom, :grade)");
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":grade", $grade);
        $stmt->execute();

        // Redirection après succès
        header("Location: ../general_admin_panel.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'insertion : " . $e->getMessage());
    }
} else {
    die("Méthode non autorisée.");
}
?>
