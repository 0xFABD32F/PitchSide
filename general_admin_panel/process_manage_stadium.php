<?php
require_once '../database_connexion.php';


// Vérifier si les champs sont envoyés
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : '';
    $ville = isset($_POST['ville']) ? htmlspecialchars(trim($_POST['ville'])) : '';
    $capacite = isset($_POST['capacite']) ? (int)$_POST['capacite'] : 0;

    if (empty($nom) || empty($ville) || $capacite <= 0) {
        die("Tous les champs sont requis et la capacité doit être positive.");
    }

    // Déterminer l'action (Update ou Drop)
    if (isset($_POST['drop_stadium'])) {
        // Supprimer le stade
        $stmt = $pdo->prepare("DELETE FROM stades WHERE nom = :nom AND ville = :ville");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':ville', $ville);

        if ($stmt->execute()) {
            echo "✅ Stade supprimé avec succès.";
            header("Location: ../general_admin_panel.php");
            exit();
        } else {
            echo "❌ Erreur lors de la suppression du stade.";
        }
    } else {
        // Ajouter ou mettre à jour le stade
        $stmt = $pdo->prepare("INSERT INTO stadiums (nom, ville, capacite) 
            VALUES (:nom, :ville, :capacite) 
            ON DUPLICATE KEY UPDATE ville = :ville, capacite = :capacite");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':capacite', $capacite);

        if ($stmt->execute()) {
            echo "✅ Stade mis à jour avec succès.";
            header("Location: ../general_admin_panel.php");
            
        } else {
            echo "❌ Erreur lors de l'ajout/mise à jour du stade.";
        }
    }
} else {
    echo "❌ Requête invalide.";
}
