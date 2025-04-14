<?php
session_start();
if (!isset($_SESSION['Type_compte']) || $_SESSION['Type_compte'] !== 'admin_tournoi') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

header('Content-Type: application/json');
include("connexion.php");

try {
    // Récupérer les données du POST
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['match_id']) || !isset($input['joueur_id']) || !isset($input['event_type']) || !isset($input['minute'])) {
        throw new Exception("Données manquantes");
    }

    $match_id = (int) $input['match_id'];
    $joueur_id = (int) $input['joueur_id'];
    $event_type = $input['event_type'];
    $minute = (int) $input['minute'];
    $details = isset($input['details']) ? $input['details'] : null;


    // Insérer l'événement
    $stmt = $conn->prepare("
        INSERT INTO match_events (match_id, joueur_id, event_type, minute, details)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$match_id, $joueur_id, $event_type, $minute, $details]);

    // Si c'est un but, mettre à jour le score
    if ($event_type === 'but') {
        $stmt = $conn->prepare("
            SELECT equipe_id FROM participations 
            WHERE match_id = ? AND joueur_id = ?
        ");
        $stmt->execute([$match_id, $joueur_id]);
        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($equipe) {
            $stmt = $conn->prepare("
                UPDATE matches 
                SET score_domicile = COALESCE(score_domicile, 0) + 1 
                WHERE id = ? AND equipe_domicile_id = ?
            ");
            $stmt->execute([$match_id, $equipe['equipe_id']]);

            if ($stmt->rowCount() === 0) {
                $stmt = $conn->prepare("
                    UPDATE matches 
                    SET score_exterieur = COALESCE(score_exterieur, 0) + 1 
                    WHERE id = ? AND equipe_exterieur_id = ?
                ");
                $stmt->execute([$match_id, $equipe['equipe_id']]);
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO notifications(utilisateur_id,message,reference_type,reference_id,statut)
        SELECT utilisateur_id,?,'match',?,'non_lu'
        FROM abonnement
        WHERE type='match' AND reference_id = ?");

    $stmt->execute([$details,$match_id, $match_id]);    

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>