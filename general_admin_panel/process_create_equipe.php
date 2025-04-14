<?php
require_once '../database_connexion.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $ville = trim($_POST['ville']);

    // Validation basique
    if (empty($nom) || empty($ville) || empty($_FILES['photo']['name'])) {
        die("❌ Veuillez remplir tous les champs et ajouter une photo.");
    }

    // Gestion de l'upload de la photo
    $photoDir = '../imgs/';
    $photoName = time() . '_' . basename($_FILES['photo']['name']);
    $photoPath = $photoDir . $photoName;

    // Validation du type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
        die("❌ Seuls les fichiers JPG, PNG et WEBP sont acceptés.");
    }

    // Validation de la taille du fichier (max 5 Mo)
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        die("❌ La taille de la photo ne doit pas dépasser 5 Mo.");
    }

    try {
        // Déplacer la photo vers le dossier cible
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            throw new Exception("Erreur lors de l'upload de la photo.");
        }
        $upload_path = preg_replace('/^(\.\.\/)+/', '', $photoPath);
        // Insérer les données dans la table equipes
        $query = "INSERT INTO equipes (nom, ville, photo) VALUES (:nom, :ville, :photo)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':nom' => $nom,
            ':ville' => $ville,
            ':photo' => $upload_path // Stocke uniquement le nom de la photo
        ]);

        // Redirection après succès
        header("Location: ../general_admin_panel.php");
        exit;
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'ajout : " . $e->getMessage();
    }
}
?>
