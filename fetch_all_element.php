<?php
require_once 'database_connexion.php';
// Fonction pour reuperer les enregistrements dans une table
function fetchAllRecords(string $table): array {
    global $pdo;
    try {
        $query = "SELECT * FROM " . htmlspecialchars($table);
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("❌ Erreur : " . $e->getMessage());
    }
}



