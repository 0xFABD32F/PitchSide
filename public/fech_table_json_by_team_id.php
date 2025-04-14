<?php
require_once '../database_connexion.php';

if (isset($_GET['team_id']) && isset($_GET['table'])) {
    $team_id = intval($_GET['team_id']);
    $table_name = $_GET['table'];

    // Vérifier que le nom de la table est valide
    $valid_tables = ['joueurs', 'staff']; // Liste des tables autorisées
    if (!in_array($table_name, $valid_tables)) {
        echo json_encode(['error' => 'Table invalide']);
        exit;
    }

    // Récupérer les enregistrements qui correspondent à team_id dans la table 
    $query = "SELECT * FROM $table_name WHERE equipe_id = :team_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Récupérer les résultats
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les enregistrements sous forme de JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>
