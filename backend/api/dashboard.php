<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $stats = [];

    // 1. Statistiques générales
    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role IN ('client', 'coach')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_membres'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'coach'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_coachs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT SUM(montant) as total FROM paiements WHERE statut = 'payé'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['revenus_totaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $query = "SELECT COUNT(*) as total FROM seances WHERE statut = 'réservée' AND date_seance >= NOW()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['seances_a_venir'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Données pour le graphique circulaire (Abonnements)
    $query = "
        SELECT a.nom_abonnement, COUNT(p.paiement_id) as total
        FROM abonnements a
        LEFT JOIN paiements p ON a.abonnement_id = p.abonnement_id
        WHERE p.statut = 'payé'
        GROUP BY a.nom_abonnement, a.abonnement_id
        ORDER BY total DESC
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['abonnements_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Données pour le graphique linéaire (Revenus par mois)
    $query = "
        SELECT 
            TO_CHAR(date_paiement, 'YYYY-MM') as mois,
            SUM(montant) as revenus
        FROM paiements 
        WHERE statut = 'payé' 
        AND date_paiement >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY TO_CHAR(date_paiement, 'YYYY-MM')
        ORDER BY mois
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['revenus_mensuels'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Données pour le graphique à barres (Nouveaux membres par mois)
    $query = "
        SELECT 
            TO_CHAR(date_creation, 'YYYY-MM') as mois,
            COUNT(*) as nouveaux_membres
        FROM utilisateurs 
        WHERE role IN ('client', 'coach')
        AND date_creation >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY TO_CHAR(date_creation, 'YYYY-MM')
        ORDER BY mois
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['nouveaux_membres'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Derniers membres inscrits
    $query = "
        SELECT nom, prenom, email, date_creation, role
        FROM utilisateurs 
        WHERE role IN ('client', 'coach')
        ORDER BY date_creation DESC 
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['derniers_membres'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Prochaines séances
    $query = "
        SELECT 
            s.date_seance,
            u_client.prenom as client_prenom,
            u_client.nom as client_nom,
            u_coach.prenom as coach_prenom,
            u_coach.nom as coach_nom,
            s.type_seance
        FROM seances s
        INNER JOIN utilisateurs u_client ON s.utilisateur_id = u_client.utilisateur_id
        INNER JOIN utilisateurs u_coach ON s.coach_id = u_coach.utilisateur_id
        WHERE s.statut = 'réservée' 
        AND s.date_seance >= NOW()
        ORDER BY s.date_seance ASC
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['prochaines_seances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
