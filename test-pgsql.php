<?php
// Test des extensions PostgreSQL
echo "<h2>Test PostgreSQL</h2>";

if (extension_loaded('pdo_pgsql')) {
    echo "✅ PDO PostgreSQL est activé<br>";
} else {
    echo "❌ PDO PostgreSQL n'est pas activé<br>";
}

if (extension_loaded('pgsql')) {
    echo "✅ PostgreSQL est activé<br>";
} else {
    echo "❌ PostgreSQL n'est pas activé<br>";
}

// Test de connexion
try {
    $db = new PDO("pgsql:host=localhost;port=5432;dbname=yabiso", "Lulu", "23525689");
    echo "✅ Connexion PostgreSQL réussie !<br>";

    // Test de requête
    $stmt = $db->query("SELECT version()");
    $version = $stmt->fetch();
    echo "Version PostgreSQL : " . $version[0];
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage();
}
