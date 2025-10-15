<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=yabiso_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test de connexion
    echo json_encode([
        'success' => true,
        'message' => 'Connexion à la base de données réussie',
        'tables' => []
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
    ]);
}
