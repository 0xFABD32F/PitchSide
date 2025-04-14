<?php
require_once '../database_connexion.php';

if (isset($_GET['team_id'])) {
    $team_id = intval($_GET['team_id']);
    
    // Récupérer les joueurs de l'équipe sélectionnée
    $query = "SELECT * FROM joueurs WHERE equipe_id = :team_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les joueurs sous forme de JSON
    header('Content-Type: application/json');
    echo json_encode($players);
    exit;
}
