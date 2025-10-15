<?php
header('Content-Type: application/json');

// Test de connexion directe
try {
    $pdo = new PDO("mysql:host=localhost;dbname=yabiso_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tester une requête simple
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Connexion directe réussie',
        'users_count' => $result['count']
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion directe: ' . $e->getMessage()
    ]);
}
