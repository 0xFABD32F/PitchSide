<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'footballdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_id']) && isset($_POST['voted_team_id'])) {
    $match_id = $_POST['match_id'];
    $voted_team_id = $_POST['voted_team_id'] === 'null' ? null : $_POST['voted_team_id'];
    #$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Vérifier si l'utilisateur a déjà voté (optionnel, uniquement pour utilisateurs connectés)
    if ($user_id !== null) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE match_id = :match_id AND user_id = :user_id");
        $stmt_check->execute(['match_id' => $match_id, 'user_id' => $user_id]);
        if ($stmt_check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà voté pour ce match.']);
            exit;
        }
    }

    // Ajouter le vote
    $stmt = $pdo->prepare("INSERT INTO votes (match_id, user_id, voted_team_id) VALUES (:match_id, :user_id, :voted_team_id)");
    $stmt->execute(['match_id' => $match_id, 'user_id' => $user_id, 'voted_team_id' => $voted_team_id]);

    // Récupérer les nouveaux résultats
    $stmt_results = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM votes v WHERE v.match_id = :match_id AND v.voted_team_id = (SELECT equipe_domicile_id FROM matches WHERE id = :match_id)) AS votes_domicile,
            (SELECT COUNT(*) FROM votes v WHERE v.match_id = :match_id AND v.voted_team_id = (SELECT equipe_exterieur_id FROM matches WHERE id = :match_id)) AS votes_exterieur,
            (SELECT COUNT(*) FROM votes v WHERE v.match_id = :match_id AND v.voted_team_id IS NULL) AS votes_nul
    ");
    $stmt_results->execute(['match_id' => $match_id]);
    $results = $stmt_results->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'results' => $results]);
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>