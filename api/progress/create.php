<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $date_mesure = $input['date_mesure'] ?? date('Y-m-d');
    $poids = $input['poids'] ?? null;
    $taille = $input['taille'] ?? null;
    $masse_musculaire = $input['masse_musculaire'] ?? null;
    $masse_graisseuse = $input['masse_graisseuse'] ?? null;
    $notes = $input['notes'] ?? '';

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "INSERT INTO progres (utilisateur_id, date_mesure, poids, taille, masse_musculaire, masse_graisseuse, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([$user_id, $date_mesure, $poids, $taille, $masse_musculaire, $masse_graisseuse, $notes]);
        $progres_id = $db->lastInsertId();

        // Logger l'action
        $auth->logActivity($_SESSION['user_id'], 'Ajout progression', "User: $user_id, Date: $date_mesure");

        echo json_encode([
            'success' => true,
            'message' => 'Progrès enregistrés avec succès',
            'data' => ['progres_id' => $progres_id]
        ]);
    } catch (PDOException $e) {
        error_log("Create progress error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement des progrès']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
