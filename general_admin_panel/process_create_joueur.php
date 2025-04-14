<?php
require_once '../database_connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $team_id = htmlspecialchars($_POST['team_id']);
    $player_nom = htmlspecialchars($_POST['player_nom']);
    $player_prenom = htmlspecialchars($_POST['player_prenom']);
    $date_naissance = htmlspecialchars($_POST['date_naissance']);
    $position = htmlspecialchars($_POST['position']);
    $numero_maillot = htmlspecialchars($_POST['numero_maillot']);
    $date_debut = htmlspecialchars($_POST['date_debut']);
    $date_fin = htmlspecialchars($_POST['date_fin']);
    $nationalite = htmlspecialchars($_POST['nationalite']);
    $origine = htmlspecialchars($_POST['origine']);

    // Validation basique
    if (
        empty($team_id) || empty($player_nom) || empty($player_prenom) || empty($date_naissance) || 
     empty($position) || empty($numero_maillot) || 
        empty($date_debut) || empty($date_fin) || empty($nationalite)  || empty($origine)
    ) {
        die("❌ Tous les champs sont obligatoires !");
    }

    if ($_FILES['photo']['error'] != UPLOAD_ERR_OK) {
        die("❌ Erreur lors du téléchargement de la photo." . $_FILES['photo']['error']);
    }

    var_dump($_FILES['photo']);

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
    // Déplacer la photo vers le dossier cible
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
        throw new Exception("Erreur lors de l'upload de la photo.");
    }
        
    try {
        // Préparation de la requête avec le champ photo
        $query = "INSERT INTO joueurs (equipe_id, nom, prenom, date_naissance, position, numero_maillot, date_debut, date_fin, nationalite,origine , photo) 
                  VALUES (:equipe_id, :nom, :prenom, :date_naissance, :position, :numero_maillot, :date_debut, :date_fin, :nationalite, :origine,:photo)";

        $stmt = $pdo->prepare($query);
        $upload_path = preg_replace('/^(\.\.\/)+/', '', $photoPath);

        // Liaison des paramètres
        $stmt->bindParam(':equipe_id', $team_id);
        $stmt->bindParam(':nom', $player_nom);
        $stmt->bindParam(':prenom', $player_prenom);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':numero_maillot', $numero_maillot);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        $stmt->bindParam(':nationalite', var: $nationalite);
        $stmt->bindParam(':origine', var: $origine);
        $stmt->bindParam(':photo', $upload_path);

        // Exécution de la requête
        $stmt->execute();
        header("Location: ../general_admin_panel.php");
        echo "✅ Joueur ajouté avec succès !";
    } catch (PDOException $e) {
        die("❌ Erreur : " . $e->getMessage());
    }
} else {
    echo "❌ Méthode non autorisée.";
}
?>
