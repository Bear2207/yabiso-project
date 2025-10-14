<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    try {
        $db = Database::getInstance()->getConnection();

        // Statistiques générales
        $stats = [];

        // Nombre de membres par statut
        $membersStmt = $db->prepare(
            "SELECT 
                COUNT(*) as total_membres,
                SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as clients,
                SUM(CASE WHEN role = 'coach' THEN 1 ELSE 0 END) as coachs,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
             FROM utilisateurs 
             WHERE statut = 'actif'"
        );
        $membersStmt->execute();
        $stats['membres'] = $membersStmt->fetch();

        // Abonnements
        $abonnementsStmt = $db->prepare(
            "SELECT 
                COUNT(*) as total_abonnements,
                SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as abonnements_actifs,
                SUM(CASE WHEN statut = 'expire' THEN 1 ELSE 0 END) as abonnements_expires
             FROM abonnements_utilisateurs"
        );
        $abonnementsStmt->execute();
        $stats['abonnements'] = $abonnementsStmt->fetch();

        // Paiements du mois
        $paiementsStmt = $db->prepare(
            "SELECT 
                COUNT(*) as paiements_mois,
                SUM(montant) as ca_mois,
                SUM(CASE WHEN mode_paiement = 'carte' THEN montant ELSE 0 END) as carte,
                SUM(CASE WHEN mode_paiement = 'espece' THEN montant ELSE 0 END) as espece,
                SUM(CASE WHEN mode_paiement = 'virement' THEN montant ELSE 0 END) as virement
             FROM paiements 
             WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE) 
             AND YEAR(date_paiement) = YEAR(CURRENT_DATE)
             AND statut = 'paye'"
        );
        $paiementsStmt->execute();
        $stats['finances'] = $paiementsStmt->fetch();

        // Séances à venir
        $seancesStmt = $db->prepare(
            "SELECT COUNT(*) as seances_avenir 
             FROM seances 
             WHERE date_seance >= CURRENT_DATE 
             AND statut = 'reservee'"
        );
        $seancesStmt->execute();
        $stats['seances'] = $seancesStmt->fetch();

        // Abonnements populaires
        $populairesStmt = $db->prepare(
            "SELECT a.nom_abonnement, COUNT(au.abonnement_id) as nombre 
             FROM abonnements_utilisateurs au 
             JOIN abonnements a ON au.abonnement_id = a.abonnement_id 
             WHERE au.statut = 'actif' 
             GROUP BY a.abonnement_id, a.nom_abonnement 
             ORDER BY nombre DESC 
             LIMIT 5"
        );
        $populairesStmt->execute();
        $stats['abonnements_populaires'] = $populairesStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des statistiques']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
