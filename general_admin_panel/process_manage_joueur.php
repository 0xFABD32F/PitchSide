<?php
require_once '../database_connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération et validation des données
        $playerId = filter_input(INPUT_POST, 'player_id', FILTER_VALIDATE_INT);
        $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
        $nom = filter_input(INPUT_POST, 'player_nom', FILTER_SANITIZE_STRING);
        $prenom = filter_input(INPUT_POST, 'player_prenom', FILTER_SANITIZE_STRING);
        $dateNaissance = filter_input(INPUT_POST, 'date_naissance');
        $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
        $dateDebut = filter_input(INPUT_POST, 'date_debut');
        $dateFin = filter_input(INPUT_POST, 'date_fin');
        $nationalite = filter_input(INPUT_POST, 'nationalite', FILTER_SANITIZE_STRING);
        $numeroMaillot = filter_input(INPUT_POST, 'numero_maillot', FILTER_VALIDATE_INT);
        $origine = filter_input(INPUT_POST, 'origine', FILTER_SANITIZE_STRING);


        if (!$playerId || !$teamId || !$nom || !$prenom || !$dateNaissance || !$position || !$dateDebut || !$dateFin || !$nationalite || !$numeroMaillot || !$origine) {
            throw new Exception('Données invalides ou manquantes.');
        }

        // Vérifie si l'action est une mise à jour ou une suppression
        if (isset($_POST['drop_player'])) {
            // Suppression du joueur
            $stmt = $pdo->prepare("DELETE FROM joueurs WHERE id = :id");
            $stmt->execute(['id' => $playerId]);

            header("Location: ../general_admin_panel.php");
            exit();
        } else {
            // Mise à jour du joueur
            $stmt = $pdo->prepare("UPDATE joueurs SET equipe_id = :team_id, nom = :nom, prenom = :prenom, date_naissance = :date_naissance, position = :position, date_debut = :date_debut, date_fin = :date_fin, nationalite = :nationalite, numero_maillot = :numero_maillot WHERE id = :id");

            $stmt->execute([
                'team_id' => $teamId,
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => $dateNaissance,
                'position' => $position,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'nationalite' => $nationalite,
                'origine' => $origine,
                'numero_maillot' => $numeroMaillot,
                'id' => $playerId
            ]);

            header("Location: ../general_admin_panel.php");
            exit();
        }

    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}

header("Location: ../general_admin_panel.php");
exit();
