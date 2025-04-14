<?php
require_once '../database_connexion.php';
// Vérifier si la requête est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $grade = trim($_POST["grade"] ?? "");

    // Validation des champs
    if (empty($nom) || empty($grade)) {
        die("Tous les champs sont requis.");
    }

    // Détecter l'action (mise à jour ou suppression)
    if (isset($_POST["update_arbitre"])) {
        // 🔄 Mettre à jour l'arbitre
        try {
            $stmt = $pdo->prepare("UPDATE arbitres SET grade = :grade WHERE nom = :nom");
            $stmt->bindParam(":nom", $nom);
            $stmt->bindParam(":grade", $grade);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                header("Location: ../general_admin_panel.php");
                echo "✅ Arbitre mis à jour avec succès.";
            } else {
                echo "⚠️ Aucun arbitre trouvé avec ce nom.";
            }
        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    } elseif (isset($_POST["drop_arbitre"])) {
        // 🗑️ Supprimer l'arbitre
        try {
            $stmt = $pdo->prepare("DELETE FROM arbitres WHERE nom = :nom");
            $stmt->bindParam(":nom", $nom);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo "✅ Arbitre supprimé avec succès.";
                header("Location: ../general_admin_panel.php");
            } else {
                echo "⚠️ Aucun arbitre trouvé avec ce nom.";
            }
        } catch (PDOException $e) {
            die("Erreur lors de la suppression : " . $e->getMessage());
        }
    } else {
        die("❌ Action non reconnue.");
    }
} else {
    die("❌ Méthode non autorisée.");
}
?>
