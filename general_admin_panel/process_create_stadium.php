<?php
require_once '../database_connexion.php';

// Validation des données du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
    $ville = htmlspecialchars(trim($_POST['ville'] ?? ''));
    $capacite = intval($_POST['capacite'] ?? 0);

    if (empty($nom) || empty($ville) || $capacite <= 0) {
        die("Veuillez remplir tous les champs correctement.");
    }

    try {
        // Préparation et exécution de la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO stades (nom, ville, capacite) VALUES (:nom, :ville, :capacite)");
        $stmt->execute([
            'nom' => $nom,
            'ville' => $ville,
            'capacite' => $capacite
        ]);
        header("Location: ../general_admin_panel.php");
        echo "✅ Stade ajouté avec succès !";
    } catch (PDOException $e) {
        die("Erreur lors de l'insertion : " . $e->getMessage());
    }
} else {
    die("Méthode non autorisée.");
}
?>
