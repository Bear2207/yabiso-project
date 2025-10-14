<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireAuth();

    try {
        $db = Database::getInstance()->getConnection();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];

        // Pour les clients, voir seulement leurs abonnements
        // Pour admin/coach, voir tous les abonnements
        if ($user_role === 'client') {
            $sql = "SELECT au.*, a.nom_abonnement, a.prix, a.duree, a.description 
                    FROM abonnements_utilisateurs au 
                    JOIN abonnements a ON au.abonnement_id = a.abonnement_id 
                    WHERE au.utilisateur_id = ? 
                    ORDER BY au.date_debut DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
        } else {
            $sql = "SELECT au.*, a.nom_abonnement, a.prix, 
                           u.prenom, u.nom, u.email 
                    FROM abonnements_utilisateurs au 
                    JOIN abonnements a ON au.abonnement_id = a.abonnement_id 
                    JOIN utilisateurs u ON au.utilisateur_id = u.utilisateur_id 
                    ORDER BY au.date_debut DESC 
                    LIMIT 100";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }

        $subscriptions = $stmt->fetchAll();

        // Statistiques pour l'admin
        $stats = [];
        if ($user_role !== 'client') {
            $statsStmt = $db->prepare(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as actifs,
                    SUM(CASE WHEN statut = 'expire' THEN 1 ELSE 0 END) as expires
                 FROM abonnements_utilisateurs"
            );
            $statsStmt->execute();
            $stats = $statsStmt->fetch();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'subscriptions' => $subscriptions,
                'stats' => $stats
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Read subscriptions error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des abonnements']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
