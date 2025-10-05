<?php
header('Content-Type: application/json');

try {
    $host = 'postgres';
    $dbname = 'yabiso_db';
    $user = 'admin';
    $pass = '23525689';

    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);

    // Tester une requête simple
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Connexion à la base de données réussie',
        'users_count' => $result['count'],
        'extensions' => get_loaded_extensions()
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage(),
        'extensions' => get_loaded_extensions()
    ]);
}
