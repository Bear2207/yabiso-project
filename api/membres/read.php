<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    try {
        $db = Database::getInstance()->getConnection();

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Filtres
        $search = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';
        $role = isset($_GET['role']) ? $_GET['role'] : 'client';
        $statut = isset($_GET['statut']) ? $_GET['statut'] : '%';

        // Compter le total
        $countStmt = $db->prepare(
            "SELECT COUNT(*) as total 
             FROM utilisateurs 
             WHERE role = ? 
             AND statut LIKE ?
             AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)"
        );
        $countStmt->execute([$role, $statut, $search, $search, $search]);
        $total = $countStmt->fetch()['total'];

        // Récupérer les membres
        $stmt = $db->prepare(
            "SELECT utilisateur_id, nom, prenom, email, telephone, date_naissance, 
                    adresse, role, statut, date_creation 
             FROM utilisateurs 
             WHERE role = ? 
             AND statut LIKE ?
             AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)
             ORDER BY date_creation DESC 
             LIMIT ? OFFSET ?"
        );

        $stmt->execute([$role, $statut, $search, $search, $search, $limit, $offset]);
        $members = $stmt->fetchAll();

        // Récupérer les abonnements actifs pour chaque membre
        foreach ($members as &$member) {
            $abonnementStmt = $db->prepare(
                "SELECT a.nom_abonnement, au.date_debut, au.date_fin, au.statut 
                 FROM abonnements_utilisateurs au 
                 JOIN abonnements a ON au.abonnement_id = a.abonnement_id 
                 WHERE au.utilisateur_id = ? AND au.statut = 'actif' 
                 ORDER BY au.date_debut DESC 
                 LIMIT 1"
            );
            $abonnementStmt->execute([$member['utilisateur_id']]);
            $member['abonnement_actuel'] = $abonnementStmt->fetch();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'members' => $members,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Read members error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des membres']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
