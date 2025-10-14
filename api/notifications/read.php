<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireAuth();

    $user_id = $_SESSION['user_id'];

    try {
        $db = Database::getInstance()->getConnection();

        // Marquer comme lues si spécifié
        if (isset($_GET['mark_read']) && $_GET['mark_read'] === 'true') {
            $updateStmt = $db->prepare(
                "UPDATE notifications 
                 SET statut = 'lu', date_lecture = CURRENT_TIMESTAMP 
                 WHERE utilisateur_id = ? AND statut = 'non_lu'"
            );
            $updateStmt->execute([$user_id]);
        }

        // Récupérer les notifications
        $stmt = $db->prepare(
            "SELECT * FROM notifications 
             WHERE utilisateur_id = ? 
             ORDER BY date_notification DESC 
             LIMIT 20"
        );
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll();

        // Compter les non lues
        $countStmt = $db->prepare(
            "SELECT COUNT(*) as unread_count 
             FROM notifications 
             WHERE utilisateur_id = ? AND statut = 'non_lu'"
        );
        $countStmt->execute([$user_id]);
        $unread_count = $countStmt->fetch()['unread_count'];

        echo json_encode([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unread_count
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Read notifications error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des notifications']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
