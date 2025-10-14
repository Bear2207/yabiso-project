<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireAuth();

    try {
        $db = Database::getInstance()->getConnection();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];

        if ($user_role === 'client') {
            $sql = "SELECT p.*, a.nom_abonnement 
                    FROM paiements p 
                    JOIN abonnements a ON p.abonnement_id = a.abonnement_id 
                    WHERE p.utilisateur_id = ? 
                    ORDER BY p.date_paiement DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
        } else {
            // Pour admin/coach avec filtres
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            $statut = isset($_GET['statut']) ? $_GET['statut'] : '%';
            $mode = isset($_GET['mode_paiement']) ? $_GET['mode_paiement'] : '%';

            $sql = "SELECT p.*, a.nom_abonnement, u.prenom, u.nom, u.email 
                    FROM paiements p 
                    JOIN abonnements a ON p.abonnement_id = a.abonnement_id 
                    JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id 
                    WHERE p.statut LIKE ? AND p.mode_paiement LIKE ? 
                    ORDER BY p.date_paiement DESC 
                    LIMIT ? OFFSET ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$statut, $mode, $limit, $offset]);
        }

        $payments = $stmt->fetchAll();

        // Statistiques financières pour l'admin
        $stats = [];
        if ($user_role !== 'client') {
            $statsStmt = $db->prepare(
                "SELECT 
                    COUNT(*) as total_paiements,
                    SUM(montant) as chiffre_affaires,
                    SUM(CASE WHEN statut = 'paye' THEN montant ELSE 0 END) as total_paye,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente
                 FROM paiements 
                 WHERE YEAR(date_paiement) = YEAR(CURRENT_DATE)"
            );
            $statsStmt->execute();
            $stats = $statsStmt->fetch();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'stats' => $stats
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Read payments error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des paiements']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
