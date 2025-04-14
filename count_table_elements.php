<?php
require_once 'database_connexion.php';
// Fonction pour compter les enregistrements dans une table
function countRecords($tableName) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM $tableName");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        die("❌ Erreur lors du comptage : " . $e->getMessage());
    }
}

// Fonction pour compter les enregistrements où une colonne a une valeur donnée
function countRecordsByColumnValue($tableName, $columnName, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM $tableName WHERE $columnName = :value");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        die("❌ Erreur lors du comptage avec condition : " . $e->getMessage());
    }
}
