<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireAuth();

    $requested_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    // Les clients ne peuvent voir que leurs propres progrès
    if ($user_role === 'client') {
        $requested_user_id = $user_id;
    }

    // Les coachs/admin peuvent voir les progrès des autres
    if (!$requested_user_id && $user_role !== 'client') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "SELECT * FROM progres 
             WHERE utilisateur_id = ? 
             ORDER BY date_mesure DESC 
             LIMIT 50"
        );

        $stmt->execute([$requested_user_id]);
        $progress = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => [
                'progress' => $progress,
                'user_id' => $requested_user_id
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Read progress error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des progrès']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
