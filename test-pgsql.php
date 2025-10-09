<?php
include_once 'backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "✅ Connexion PostgreSQL réussie!";

    // Test de requête
    $stmt = $db->query("SELECT version()");
    $version = $stmt->fetch();
    echo "<br>Version PostgreSQL: " . $version[0];
} else {
    echo "❌ Échec de connexion PostgreSQL";
}
