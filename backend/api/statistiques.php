<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Statistiques générales
    $stats = [];

    // Total membres
    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role IN ('client', 'coach')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_membres'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total coachs
    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'coach'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_coachs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Paiements du mois
    $query = "SELECT COUNT(*) as total, COALESCE(SUM(montant), 0) as revenue 
              FROM paiements 
              WHERE date_paiement >= DATE_TRUNC('month', CURRENT_DATE) 
              AND statut = 'payé'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $paiements_mois = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['paiements_mois'] = $paiements_mois['total'];
    $stats['revenue_mois'] = $paiements_mois['revenue'];

    // Séances à venir
    $query = "SELECT COUNT(*) as total FROM seances WHERE date_seance >= NOW() AND statut = 'réservée'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['seances_avenir'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Abonnements populaires
    $query = "SELECT a.nom_abonnement, COUNT(p.paiement_id) as total
              FROM abonnements a
              LEFT JOIN paiements p ON a.abonnement_id = p.abonnement_id
              GROUP BY a.abonnement_id, a.nom_abonnement
              ORDER BY total DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['abonnements_populaires'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
