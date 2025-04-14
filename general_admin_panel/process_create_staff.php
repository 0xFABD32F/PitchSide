<?php

require_once '../database_connexion.php';

// Validation des données
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = filter_input(INPUT_POST, 'team_id', FILTER_SANITIZE_NUMBER_INT);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $date_naissance = filter_input(INPUT_POST, 'date_naissance', FILTER_SANITIZE_STRING);
    $post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_STRING);
    $date_debut = filter_input(INPUT_POST, 'date_debut', FILTER_SANITIZE_STRING);
    $date_fin = filter_input(INPUT_POST, 'date_fin', FILTER_SANITIZE_STRING);

    // Vérification des champs requis
    if (!$team_id || !$nom || !$prenom || !$date_naissance || !$post || !$date_debut || !$date_fin) {
        die("Tous les champs sont requis.");
    }

    try {
        // Insertion des données
        $sql = "INSERT INTO staff (equipe_id, nom, prenom, date_naissance, poste, date_debut, date_fin) 
                VALUES (:team_id, :nom, :prenom, :date_naissance, :post, :date_debut, :date_fin)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':team_id' => $team_id,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':date_naissance' => $date_naissance,
            ':post' => $post,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        header("Location: ../general_admin_panel.php");

        echo "✅ Staff ajouté avec succès.";

    } catch (PDOException $e) {
        die("Erreur lors de l'insertion : " . $e->getMessage());
    }
} else {
    echo "Méthode non autorisée.";
}

?>
