<?php
header('Content-Type: application/json');

try {
    // Test de connexion sans base spécifique
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la base existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'yabiso_db'");
    $dbExists = $stmt->fetch();

    if ($dbExists) {
        // Tester la connexion à la base spécifique
        $pdo2 = new PDO("mysql:host=localhost;dbname=yabiso_db", "root", "");
        $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier les tables
        $tablesStmt = $pdo2->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'message' => 'Base de données yabiso_db existe',
            'tables' => $tables
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'La base de données yabiso_db n\'existe pas'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion MySQL: ' . $e->getMessage()
    ]);
}
