<?php
require_once '../database_connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs requis
    if (empty($_POST['admin_type']) || empty($_POST['user_id'])) {
        die('Erreur : Tous les champs sont requis.');
    }

    $adminType = trim($_POST['admin_type']);
    $userid = intval($_POST['user_id']);

    // Liste des types autorisés pour éviter les injections
    $allowedTypes = ['user', 'admin_global', 'admin_tournoi'];

    if (!in_array($adminType, $allowedTypes)) {
        die('Erreur : Type dadmin invalide.');
    }

    try {
        // Vérification si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id_compte FROM comptes WHERE id_compte = :id");
        $stmt->bindParam(':id', $userid, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            die('Erreur : Utilisateur introuvable.');
        }

        // Mise à jour du type de l'utilisateur
        $updateStmt = $pdo->prepare("UPDATE comptes SET type_compte = :type WHERE id_compte = :id");
        $updateStmt->bindParam(':type', $adminType, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $userid, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            header("Location: ../general_admin_panel.php");
            exit();
        } else {
            echo '❌ Erreur lors de la mise à jour du type dutilisateur.';
        }
    } catch (PDOException $e) {
        die('Erreur de connexion : ' . $e->getMessage());
    }
} else {
    echo 'Accès non autorisé.';
}
?>