<?php
require_once '../database_connexion.php';

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $ville = isset($_POST['ville']) ? trim($_POST['ville']) : '';

    // Validation basique
    if ($team_id <= 0) {
        die("❌ Sélectionnez une équipe valide.");
    }

    try {
        // Si le bouton "Update" est cliqué
        if (isset($_POST['nom']) && isset($_POST['ville'])) {
            if (empty($nom) || empty($ville)) {
                die("❌ Veuillez remplir tous les champs.");
            }

            $query = "UPDATE equipes SET nom = :nom, ville = :ville WHERE id = :team_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':ville' => $ville,
                ':team_id' => $team_id
            ]);
            echo "✅ Équipe mise à jour avec succès.";
        }

        // Si le bouton "Drop Team" est cliqué
        if (isset($_POST['drop_team'])) {
            $query = "DELETE FROM equipes WHERE id = :team_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':team_id' => $team_id]);
            echo "✅ Équipe supprimée avec succès.";
        }
        header("Location: ../general_admin_panel.php");
    } catch (PDOException $e) {
        die("❌ Erreur : " . $e->getMessage());
    }
}
