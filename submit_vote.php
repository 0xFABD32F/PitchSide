<?php
session_start();
require 'database_connexion.php'; // Assurez-vous d'inclure votre fichier de connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Erreur : Vous devez être connecté pour voter.");
    }

    $user_id = $_SESSION['user_id'];
    $match_id = $_POST['match_id'] ?? null;
    $voted_team_id = $_POST['voted_team_id'] ?? null;

    if (empty($match_id) || is_null($voted_team_id)) {
        die("Erreur : Données invalides.");
    }

    if ($voted_team_id === "null") {
        $voted_team_id = NULL;
    }

    try {


        // Vérifier si l'utilisateur a déjà voté pour ce match
        $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = :user_id AND match_id = :match_id");
        $stmt->execute(['user_id' => $user_id, 'match_id' => $match_id]);

        if ($stmt->rowCount() > 0) {
            header(header: "Location: acceuil.php"); // Crée une page de confirmation si besoin
        }

        // Insérer le vote
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, match_id, voted_team_id) VALUES (:user_id, :match_id, :voted_team_id)");
        $stmt->execute([
            'user_id' => $user_id,
            'match_id' => $match_id,
            'voted_team_id' => $voted_team_id
        ]);
        header(header: "Location: acceuil.php"); // Crée une page de confirmation si besoin
        echo "Vote soumis avec succès !";
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
} else {
    die("Erreur : Requête invalide.");
}
?>
