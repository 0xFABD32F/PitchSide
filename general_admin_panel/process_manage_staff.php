<?php
require_once '../database_connexion.php';
/*
npm install -g @nestjs/cli 
nest new nom-du-projet
npm run start 

*/
// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Vérifier si l'action est de mettre à jour un membre du personnel
    if (isset($_POST['update_staff'])) {

        // Récupérer les données du formulaire avec validation et nettoyage
        $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
        $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $date_naissance = htmlspecialchars(trim($_POST['date_naissance']));
        $post = htmlspecialchars(trim($_POST['post']));
        $date_debut = htmlspecialchars(trim($_POST['date_debut']));
        $date_fin = htmlspecialchars(trim($_POST['date_fin']));

        // Vérification si les ID sont valides
        if ($team_id === false || $staff_id === false) {
            echo "ID de l'équipe ou du personnel invalide.";
            exit();
        }

        // Mettre à jour les informations du staff dans la base de données
        $query = "UPDATE staff SET equipe_id = ?, nom = ?, prenom = ?, date_naissance = ?, post = ?, date_debut = ?, date_fin = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);

        if ($stmt) {
            // Lier les paramètres et exécuter la requête
            $stmt->bindValue(1, $team_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $nom, PDO::PARAM_STR);
            $stmt->bindValue(3, $prenom, PDO::PARAM_STR);
            $stmt->bindValue(4, $date_naissance, PDO::PARAM_STR);
            $stmt->bindValue(5, $post, PDO::PARAM_STR);
            $stmt->bindValue(6, $date_debut, PDO::PARAM_STR);
            $stmt->bindValue(7, $date_fin, PDO::PARAM_STR);
            $stmt->bindValue(8, $staff_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Le personnel a été mis à jour avec succès.";
                header("Location: ../general_admin_panel.php");
                exit();
            } else {
                echo "Erreur lors de la mise à jour du personnel : " . $stmt->errorInfo()[2];
            }
            $stmt->closeCursor();
        } else {
            echo "Erreur de préparation de la requête.";
        }
    }

    // Vérifier si l'action est de supprimer un membre du personnel
    if (isset($_POST['drop_staff'])) {

        // Récupérer l'id du personnel à supprimer
        $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);

        if ($staff_id === false) {
            echo "ID du personnel invalide.";
            exit();
        }

        // Supprimer le personnel de la base de données
        $query = "DELETE FROM staff WHERE id = ?";
        $stmt = $pdo->prepare($query);

        if ($stmt) {
            $stmt->bindValue(1, $staff_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Le personnel a été supprimé avec succès.";
                header("Location: ../general_admin_panel.php");
                exit();
            } else {
                echo "Erreur lors de la suppression du personnel : " . $stmt->errorInfo()[2];
            }
            $stmt->closeCursor();
        } else {
            echo "Erreur de préparation de la requête.";
        }
    }
}
?>
